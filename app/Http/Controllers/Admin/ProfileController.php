<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Hiển thị trang hồ sơ admin.
     */
    public function edit(): View
    {
        $admin = Auth::guard('admin')->user();
        return view('admin.profile.edit', compact('admin'));
    }

    /**
     * Cập nhật thông tin cá nhân admin.
     */
    public function update(Request $request): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();

        $validated = $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'email'  => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($admin->id)],
            'phone'  => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/', Rule::unique('users', 'phone')->ignore($admin->id)],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:3072'],
        ], [
            'name.required'  => 'Vui lòng nhập họ tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email'    => 'Email không hợp lệ.',
            'email.unique'   => 'Email đã được sử dụng bởi tài khoản khác.',
            'phone.regex'    => 'Số điện thoại không hợp lệ.',
            'phone.unique'   => 'Số điện thoại đã được sử dụng.',
            'avatar.image'   => 'Avatar phải là hình ảnh.',
            'avatar.mimes'   => 'Chỉ hỗ trợ jpg, jpeg, png, webp, gif.',
            'avatar.max'     => 'Avatar không được vượt quá 3MB.',
        ]);

        $updateData = [
            'name'  => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ];

        if ($request->hasFile('avatar')) {
            // Xóa ảnh cũ nếu là ảnh đã upload (không phải placeholder)
            if ($admin->avatar && str_starts_with($admin->avatar, '/storage/avatars/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $admin->avatar));
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $updateData['avatar'] = Storage::url($path);
        }

        $admin->update($updateData);

        return redirect()->route('admin.profile.edit')
            ->with('success', 'Cập nhật thông tin cá nhân thành công.');
    }

    /**
     * Đổi mật khẩu admin.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();

        $validated = $request->validateWithBag('passwordUpdate', [
            'current_password' => ['required', 'current_password:admin'],
            'password'         => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.required'        => 'Vui lòng nhập mật khẩu hiện tại.',
            'current_password.current_password'=> 'Mật khẩu hiện tại không chính xác.',
            'password.required'                => 'Vui lòng nhập mật khẩu mới.',
            'password.min'                     => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'password.confirmed'               => 'Xác nhận mật khẩu mới không khớp.',
        ]);

        $admin->update(['password' => Hash::make($validated['password'])]);

        return redirect()->route('admin.profile.edit')
            ->with('password_success', 'Đổi mật khẩu thành công.');
    }
}
