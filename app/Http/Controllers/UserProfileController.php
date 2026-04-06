<?php

namespace App\Http\Controllers;

use App\Mail\EmailChangeVerification;
use App\Mail\PasswordChangeVerification;
use App\Repositories\UserRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class UserProfileController extends Controller
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update profile (name, phone, birthday, gender, avatar).
     * Email is NO LONGER changed here – it uses a separate verification flow.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'phone'    => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/', Rule::unique('users', 'phone')->ignore($user->id)],
            'birthday' => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
            'gender'   => ['nullable', 'string', 'in:Nam,Nữ,Khác'],
            'avatar'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:3072'],
        ], [
            'name.required'  => 'Vui lòng nhập họ tên.',
            'phone.regex'    => 'Số điện thoại không hợp lệ.',
            'phone.unique'   => 'Số điện thoại đã được sử dụng.',
            'birthday.before' => 'Ngày sinh phải trước hôm nay.',
            'gender.in'      => 'Giới tính không hợp lệ.',
            'avatar.image'   => 'Avatar phải là hình ảnh.',
            'avatar.mimes'   => 'Avatar chỉ hỗ trợ jpg, jpeg, png, webp, gif.',
            'avatar.max'     => 'Avatar không được vượt quá 3MB.',
        ]);

        $updateData = [
            'name'     => $validated['name'],
            'phone'    => $validated['phone'] ?? null,
            'birthday' => $validated['birthday'] ?? null,
            'gender'   => $validated['gender'] ?? null,
        ];

        if ($request->hasFile('avatar')) {
            if ($user->avatar && str_starts_with($user->avatar, '/storage/avatars/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $user->avatar));
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $updateData['avatar'] = Storage::url($path);
        }

        $this->userRepository->updateProfile($user, $updateData);

        return redirect()->route('profile.edit')
            ->with('success', 'Cập nhật thông tin cá nhân thành công.');
    }

    // ─── Email Change via Verification ─────────────────────────────────────

    /**
     * Step 1: User requests email change → send verification email to CURRENT email.
     * POST /profile/email
     */
    public function requestEmailChange(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validateWithBag('emailUpdate', [
            'new_email'        => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'email_password'   => ['required', 'current_password'],
        ], [
            'new_email.required'        => 'Vui lòng nhập email mới.',
            'new_email.email'           => 'Email mới không hợp lệ.',
            'new_email.unique'          => 'Email mới đã được sử dụng bởi tài khoản khác.',
            'email_password.required'   => 'Vui lòng nhập mật khẩu xác nhận.',
            'email_password.current_password' => 'Mật khẩu xác nhận không chính xác.',
        ]);

        $newEmail = $validated['new_email'];

        // Same email → no need to change
        if ($newEmail === $user->email) {
            return redirect()->route('profile.edit')
                ->with('email_info', 'Email mới trùng với email hiện tại, không cần thay đổi.');
        }

        // Store pending change in cache (60 min TTL)
        $cacheKey = 'email_change_' . $user->id;
        Cache::put($cacheKey, $newEmail, now()->addMinutes(60));

        // Create signed URL
        $verificationUrl = URL::temporarySignedRoute(
            'profile.email.verify',
            now()->addMinutes(60),
            ['user' => $user->id, 'hash' => sha1($newEmail)]
        );

        // Send verification to CURRENT email
        Mail::to($user->email)->send(new EmailChangeVerification($user, $newEmail, $verificationUrl));

        return redirect()->route('profile.edit')
            ->with('email_success', 'Đã gửi email xác nhận đến ' . $user->email . '. Vui lòng kiểm tra hộp thư để xác nhận thay đổi.');
    }

    /**
     * Step 2: User clicks the verification link in email.
     * GET /profile/email/verify/{user}/{hash}
     */
    public function verifyEmailChange(Request $request, int $userId, string $hash): RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            return redirect()->route('profile.edit')
                ->withErrors(['email' => 'Liên kết xác nhận đã hết hạn hoặc không hợp lệ.']);
        }

        $user = $request->user();

        if (!$user || (int) $user->id !== $userId) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Vui lòng đăng nhập đúng tài khoản để xác nhận thay đổi email.']);
        }

        $cacheKey = 'email_change_' . $user->id;
        $newEmail = Cache::get($cacheKey);

        if (! $newEmail) {
            return redirect()->route('profile.edit')
                ->withErrors(['email' => 'Yêu cầu thay đổi email đã hết hạn. Vui lòng thử lại.']);
        }

        // Verify hash
        if ($hash !== sha1($newEmail)) {
            return redirect()->route('profile.edit')
                ->withErrors(['email' => 'Liên kết xác nhận không hợp lệ.']);
        }

        // Check if email is still available
        $emailTaken = \App\Models\User::where('email', $newEmail)
            ->where('id', '!=', $user->id)
            ->where('deleted', false)
            ->exists();

        if ($emailTaken) {
            Cache::forget($cacheKey);
            return redirect()->route('profile.edit')
                ->withErrors(['email' => 'Email mới đã được sử dụng bởi tài khoản khác.']);
        }

        // Apply the email change
        $this->userRepository->updateProfile($user, [
            'email'             => $newEmail,
            'email_verified_at' => null,
        ]);

        Cache::forget($cacheKey);

        // Send email verification for the new address
        $user->refresh();
        $user->sendEmailVerificationNotification();

        return redirect()->route('profile.edit')
            ->with('success', 'Email đã được thay đổi thành công sang ' . $newEmail . '. Vui lòng xác minh email mới.');
    }

    // ─── Password Change via Verification ──────────────────────────────────

    /**
     * Step 1: User requests password change → send verification email.
     * POST /profile/password
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validateWithBag('passwordUpdate', [
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::defaults()],
        ], [
            'current_password.required'         => 'Vui lòng nhập mật khẩu hiện tại.',
            'current_password.current_password' => 'Mật khẩu hiện tại không chính xác.',
            'password.required'                 => 'Vui lòng nhập mật khẩu mới.',
            'password.confirmed'                => 'Xác nhận mật khẩu mới không khớp.',
        ]);

        // Store the new (hashed) password in cache pending verification
        $cacheKey = 'password_change_' . $user->id;
        Cache::put($cacheKey, Hash::make($validated['password']), now()->addMinutes(60));

        // Build signed verification URL
        $verificationUrl = URL::temporarySignedRoute(
            'profile.password.verify',
            now()->addMinutes(60),
            ['user' => $user->id, 'hash' => Str::random(40)]
        );

        // Send verification email
        Mail::to($user->email)->send(new PasswordChangeVerification($user, $verificationUrl));

        return redirect()->route('profile.edit')
            ->with('password_success', 'Đã gửi email xác nhận đổi mật khẩu đến ' . $user->email . '. Vui lòng kiểm tra hộp thư để xác nhận.');
    }

    /**
     * Step 2: User clicks verification link → password is applied.
     * GET /profile/password/verify/{user}/{hash}
     */
    public function verifyPasswordChange(Request $request, int $userId, string $hash): RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            return redirect()->route('profile.edit')
                ->withErrors(['password' => 'Liên kết xác nhận đã hết hạn hoặc không hợp lệ.'], 'passwordUpdate');
        }

        $user = $request->user();

        if (!$user || (int) $user->id !== $userId) {
            return redirect()->route('login')
                ->withErrors(['password' => 'Vui lòng đăng nhập đúng tài khoản để xác nhận đổi mật khẩu.']);
        }

        $cacheKey = 'password_change_' . $user->id;
        $hashedPassword = Cache::get($cacheKey);

        if (! $hashedPassword) {
            return redirect()->route('profile.edit')
                ->withErrors(['password' => 'Yêu cầu đổi mật khẩu đã hết hạn. Vui lòng thử lại.'], 'passwordUpdate');
        }

        // Apply the password change directly (already hashed)
        \Illuminate\Support\Facades\DB::table('users')
            ->where('id', $user->id)
            ->update(['password' => $hashedPassword]);

        $user->refresh();

        $this->userRepository->createHistory(
            $user->id,
            $user->id,
            'Đổi mật khẩu tài khoản (xác nhận qua email)',
            $user->status
        );

        Cache::forget($cacheKey);

        return redirect()->route('profile.edit')
            ->with('password_success', 'Mật khẩu đã được đổi thành công.');
    }

    // ─── Email Verification (existing) ─────────────────────────────────────

    public function showVerificationNotice(Request $request): RedirectResponse|View
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('profile.edit')
                ->with('success', 'Email của bạn đã được xác minh.');
        }

        return view('auth.verify-email');
    }

    public function sendVerificationNotification(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('profile.edit')
                ->with('success', 'Email đã được xác minh trước đó.');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Đã gửi lại email xác minh. Vui lòng kiểm tra hộp thư.');
    }

    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        if (! $request->user()->hasVerifiedEmail()) {
            $request->fulfill();

            $this->userRepository->createHistory(
                $request->user()->id,
                $request->user()->id,
                'Xác minh email thành công',
                $request->user()->status
            );
        }

        return redirect()->route('profile.edit')
            ->with('success', 'Xác minh email thành công.');
    }
}
