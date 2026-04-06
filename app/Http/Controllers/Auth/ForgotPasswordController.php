<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Show the forgot password form.
     * GET /forgot-password
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Send the password reset email.
     * POST /forgot-password
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email'    => 'Email không hợp lệ.',
        ]);

        $user = User::where('email', $request->email)
            ->where('deleted', false)
            ->first();

        // Always show success message to prevent email enumeration
        if (! $user) {
            return back()->with('success', 'Nếu email tồn tại trong hệ thống, chúng tôi đã gửi liên kết đặt lại mật khẩu. Vui lòng kiểm tra hộp thư.');
        }

        // Check rate limit: only allow one reset email per 2 minutes
        $recentToken = DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->where('created_at', '>', now()->subMinutes(2))
            ->first();

        if ($recentToken) {
            return back()->with('success', 'Nếu email tồn tại trong hệ thống, chúng tôi đã gửi liên kết đặt lại mật khẩu. Vui lòng kiểm tra hộp thư.');
        }

        // Generate token
        $token = Str::random(64);

        // Delete any existing tokens for this email
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        // Store the new token
        DB::table('password_reset_tokens')->insert([
            'email'      => $user->email,
            'token'      => Hash::make($token),
            'created_at' => now(),
        ]);

        // Build the reset URL
        $resetUrl = URL::temporarySignedRoute(
            'password.reset',
            now()->addMinutes(60),
            ['token' => $token, 'email' => $user->email]
        );

        // Send email
        Mail::to($user->email)->send(new PasswordResetMail($user, $resetUrl));

        return back()->with('success', 'Nếu email tồn tại trong hệ thống, chúng tôi đã gửi liên kết đặt lại mật khẩu. Vui lòng kiểm tra hộp thư.');
    }

    /**
     * Show the reset password form.
     * GET /reset-password/{token}
     */
    public function edit(Request $request): View|RedirectResponse
    {
        // Verify signed URL
        if (! $request->hasValidSignature()) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.']);
        }

        $email = $request->query('email');
        $token = $request->route('token');

        return view('auth.reset-password', [
            'email' => $email,
            'token' => $token,
        ]);
    }

    /**
     * Reset the user's password.
     * POST /reset-password
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'email.required'    => 'Email không hợp lệ.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        // Find the token record
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (! $record || ! Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => 'Token đặt lại mật khẩu không hợp lệ.']);
        }

        // Check if token is expired (60 minutes)
        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Liên kết đặt lại mật khẩu đã hết hạn. Vui lòng gửi yêu cầu mới.']);
        }

        // Find user
        $user = User::where('email', $request->email)
            ->where('deleted', false)
            ->first();

        if (! $user) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Không tìm thấy tài khoản với email này.']);
        }

        // Update the password
        $this->userRepository->updatePassword($user, $request->password);

        // Delete the token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')
            ->with('success', 'Mật khẩu đã được đặt lại thành công. Vui lòng đăng nhập với mật khẩu mới.');
    }
}
