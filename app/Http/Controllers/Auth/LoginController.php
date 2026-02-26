<?php

namespace App\Http\Controllers\Auth;

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
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Vui lòng nhập địa chỉ email hợp lệ.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        // Find user by email
        $user = $this->userRepository->findByEmail($credentials['email']);

        // Check if user exists
        if (!$user) {
            return back()->withErrors([
                'email' => 'Email hoặc mật khẩu không chính xác.',
            ])->onlyInput('email');
        }

        // Check if user account is active
        if (!$user->isActive()) {
            // Phân biệt tài khoản bị khóa tạm thời và bị vô hiệu hóa vĩnh viễn
            if ($user->isLocked()) {
                return back()
                    ->withErrors([
                        'email' => 'Tài khoản của bạn đang bị khóa. Bạn có thể gửi yêu cầu mở khóa bên dưới.',
                    ])
                    ->onlyInput('email')
                    ->with('show_unlock_link', true)
                    ->with('locked_email', $credentials['email']);
            }
            return back()->withErrors([
                'email' => 'Tài khoản của bạn đã bị vô hiệu hóa. Vui lòng liên hệ quản trị viên.',
            ])->onlyInput('email');
        }

        // Attempt to authenticate
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Create login history
            $this->userRepository->updateLastLogin($user);

            return redirect()->intended('/')->with('success', 'Chào mừng bạn trở lại! 🎵');
        }

        return back()->withErrors([
            'email' => 'Email hoặc mật khẩu không chính xác.',
        ])->onlyInput('email');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // Create logout history
        if ($user) {
            $this->userRepository->createHistory(
                $user->id,
                $user->id,
                'Đăng xuất khỏi hệ thống',
                $user->status
            );
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Bạn đã đăng xuất thành công.');
    }
}
