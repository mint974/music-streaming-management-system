<?php

namespace App\Http\Middleware;

use App\Repositories\UserRepository;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Log activity only for authenticated users
        if (Auth::check()) {
            $user = Auth::user();
            $action = $this->getActionDescription($request);

            // Only log specific actions (avoid logging every single request)
            if ($action) {
                $this->userRepository->createHistory(
                    $user->id,
                    $user->id,
                    $action,
                    $user->status
                );
            }
        }

        return $response;
    }

    /**
     * Get action description based on request.
     *
     * @param Request $request
     * @return string|null
     */
    protected function getActionDescription(Request $request): ?string
    {
        $method = $request->method();
        $path = $request->path();

        // Map routes to actions
        $actionMap = [
            'profile' => [
                'GET' => null, // Don't log profile views
                'PUT' => 'Cập nhật thông tin profile',
                'PATCH' => 'Cập nhật thông tin profile',
            ],
            'password' => [
                'PUT' => 'Thay đổi mật khẩu',
                'PATCH' => 'Thay đổi mật khẩu',
            ],
            'avatar' => [
                'POST' => 'Thay đổi ảnh đại diện',
                'PUT' => 'Thay đổi ảnh đại diện',
            ],
        ];

        foreach ($actionMap as $route => $methods) {
            if (str_contains($path, $route) && isset($methods[$method])) {
                return $methods[$method];
            }
        }

        return null;
    }
}
