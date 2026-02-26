<?php

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/', Rule::unique('users', 'phone')->ignore($user->id)],
            'birthday' => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
            'gender' => ['nullable', 'string', 'in:Nam,Nữ,Khác'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:3072'],
        ], [
            'name.required' => 'Vui lòng nhập họ tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email đã được sử dụng.',
            'phone.regex' => 'Số điện thoại không hợp lệ.',
            'phone.unique' => 'Số điện thoại đã được sử dụng.',
            'birthday.before' => 'Ngày sinh phải trước hôm nay.',
            'gender.in' => 'Giới tính không hợp lệ.',
            'avatar.image' => 'Avatar phải là hình ảnh.',
            'avatar.mimes' => 'Avatar chỉ hỗ trợ jpg, jpeg, png, webp, gif.',
            'avatar.max' => 'Avatar không được vượt quá 3MB.',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'birthday' => $validated['birthday'] ?? null,
            'gender' => $validated['gender'] ?? null,
        ];

        $emailChanged = $updateData['email'] !== $user->email;

        if ($request->hasFile('avatar')) {
            if ($user->avatar && str_starts_with($user->avatar, '/storage/avatars/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $user->avatar));
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $updateData['avatar'] = Storage::url($path);
        }

        if ($emailChanged) {
            $updateData['email_verified_at'] = null;
        }

        $this->userRepository->updateProfile($user, $updateData);

        if ($emailChanged) {
            $user->refresh();
            $user->sendEmailVerificationNotification();

            return redirect()->route('profile.edit')
                ->with('success', 'Cập nhật hồ sơ thành công. Email đã thay đổi, vui lòng xác minh lại email mới.');
        }

        return redirect()->route('profile.edit')
            ->with('success', 'Cập nhật thông tin cá nhân thành công.');
    }

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

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validateWithBag('passwordUpdate', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'current_password.current_password' => 'Mật khẩu hiện tại không chính xác.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
        ]);

        $updated = $this->userRepository->updatePassword($user, $validated['password']);

        if (! $updated) {
            return redirect()->route('profile.edit')
                ->withErrors(['password' => 'Không thể cập nhật mật khẩu. Vui lòng thử lại.'], 'passwordUpdate');
        }

        return redirect()->route('profile.edit')
            ->with('password_success', 'Đổi mật khẩu thành công.');
    }
}
