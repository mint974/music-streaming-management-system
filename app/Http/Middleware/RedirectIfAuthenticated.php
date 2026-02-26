<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  ...$guards
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                /** @var User|null $user */
                $user = Auth::guard($guard)->user();

                if ($user && $user->isActive()) {
                    // Redirect to the appropriate dashboard based on the guard used
                    return $guard === 'admin'
                        ? redirect()->route('admin.dashboard')
                        : redirect('/');
                }

                // Account inactive — clear the guard session and send back to the right login page
                Auth::guard($guard)->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $loginRoute = $guard === 'admin' ? 'admin.login' : 'login';

                return redirect()->route($loginRoute)
                    ->with('error', 'Tài khoản của bạn đã bị vô hiệu hóa.');
            }
        }

        return $next($request);
    }
}
