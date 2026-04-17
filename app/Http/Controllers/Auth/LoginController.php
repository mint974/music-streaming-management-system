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

        // We will attempt authentication first, then check status.

        // Attempt to authenticate
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            // Kiểm tra trạng thái lịch sử mới nhất
            $latestHistory = \App\Models\AccountHistory::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->first();
            $latestStatus = $latestHistory->status ?? $user->status;

            if ($latestStatus === 'Đang yêu cầu khôi phục') {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()
                    ->withErrors(['email' => 'Tài khoản đang chờ khôi phục. Vui lòng đợi quản trị viên duyệt.'])
                    ->onlyInput('email');
            }

            if ($latestStatus === 'Bị khóa' || $latestStatus === 'Bị vô hiệu hóa' || $user->isLocked()) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()
                    ->withErrors([
                        'email' => 'Tài khoản của bạn đã bị vô hiệu hóa.',
                    ])
                    ->onlyInput('email')
                    ->with('show_unlock_link', true)
                    ->with('locked_email', $credentials['email']);
            }

            if (!$user->isActive()) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors([
                    'email' => 'Tài khoản của bạn đã bị vô hiệu hóa. Vui lòng liên hệ quản trị viên.',
                ])->onlyInput('email');
            }

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
