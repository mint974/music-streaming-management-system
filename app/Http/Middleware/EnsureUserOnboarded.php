<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserOnboarded
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (\Illuminate\Support\Facades\Auth::check()) {
            $user = \Illuminate\Support\Facades\Auth::user();
            if (!$user->isAdmin() && !$user->is_onboarded) {
                // Nếu user đã có lịch sử nghe nhạc, tự động pass Onboarding luôn
                if ($user->listeningHistories()->exists()) {
                    $user->is_onboarded = true;
                    $user->save();
                    return $next($request);
                }

                if (!$request->routeIs('onboarding.*') && !$request->routeIs('logout')) {
                    return redirect()->route('onboarding.index');
                }
            }
        }
        return $next($request);
    }
}
