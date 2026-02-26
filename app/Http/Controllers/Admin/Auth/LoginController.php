<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Display the admin login form.
     */
    public function create(): View
    {
        return view('admin.auth.login');
    }

    /**
     * Handle an incoming admin authentication request.
     * Uses the 'admin' guard — completely separate session from the 'web' (user) guard.
     */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'Vui lòng nhập địa chỉ email.',
            'email.email'       => 'Địa chỉ email không hợp lệ.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        // Find user by email
        $user = $this->userRepository->findByEmail($credentials['email']);

        // User must exist
        if (! $user) {
            return back()->withErrors([
                'email' => 'Email hoặc mật khẩu không chính xác.',
            ])->onlyInput('email');
        }

        // Must be admin role
        if (! $user->isAdmin()) {
            return back()->withErrors([
                'email' => 'Tài khoản này không có quyền truy cập trang quản trị.',
            ])->onlyInput('email');
        }

        // Must be active
        if (! $user->isActive()) {
            return back()->withErrors([
                'email' => 'Tài khoản của bạn đã bị vô hiệu hóa. Vui lòng liên hệ hỗ trợ.',
            ])->onlyInput('email');
        }

        // Attempt login via the dedicated 'admin' guard
        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $this->userRepository->updateLastLogin($user);
            $this->userRepository->createHistory(
                $user->id,
                $user->id,
                'Đăng nhập vào trang quản trị',
                $user->status
            );

            return redirect()->intended(route('admin.dashboard'))
                ->with('success', 'Chào mừng trở lại, ' . $user->name . '!');
        }

        return back()->withErrors([
            'email' => 'Email hoặc mật khẩu không chính xác.',
        ])->onlyInput('email');
    }

    /**
     * Destroy the admin authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::guard('admin')->user();

        if ($user) {
            $this->userRepository->createHistory(
                $user->id,
                $user->id,
                'Đăng xuất khỏi trang quản trị',
                $user->status
            );
        }

        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')
            ->with('success', 'Bạn đã đăng xuất khỏi trang quản trị.');
    }
}
