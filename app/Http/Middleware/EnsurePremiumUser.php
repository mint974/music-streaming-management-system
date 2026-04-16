<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePremiumUser
{
    /**
     * Ensure current user can access premium-only features.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để tiếp tục.');
        }

        if (! $user->canAccessPremium()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Tính năng này chỉ dành cho tài khoản Premium.',
                ], 403);
            }

            return redirect()->route('subscription.index')
                ->with('error', 'Tính năng tải nhạc về máy chỉ dành cho tài khoản Premium.');
        }

        return $next($request);
    }
}