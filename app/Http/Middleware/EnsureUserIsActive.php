<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $guard  Which guard to check (default: web).
     */
    public function handle(Request $request, Closure $next, string $guard = 'web'): Response
    {
        /** @var User|null $user */
        $user = Auth::guard($guard)->user();

        if (! $user) {
            $loginRoute = $guard === 'admin' ? 'admin.login' : 'login';
            return redirect()->route($loginRoute)->with('error', 'Vui lòng đăng nhập để tiếp tục.');
        }

        if (! $user->isActive()) {
            Auth::guard($guard)->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $loginRoute = $guard === 'admin' ? 'admin.login' : 'login';
            return redirect()->route($loginRoute)
                ->with('error', 'Tài khoản của bạn đã bị vô hiệu hóa. Vui lòng liên hệ quản trị viên.');
        }

        return $next($request);
    }
}