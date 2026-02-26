<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Trả về user đang đăng nhập với kiểu User (thay vì Authenticatable|null).
     * Giải quyết Intelephense P1013 do Auth::user() trả về kiểu chung.
     */
    private function currentUser(): User
    {
        /** @var User $user */
        $user = Auth::user();
        return $user;
    }

    /**
     * Hiển thị tất cả thông báo của người dùng đang đăng nhập.
     */
    public function index(): View
    {
        $user          = $this->currentUser();
        $notifications = $user->notifications()->latest()->paginate(20);

        // Đánh dấu tất cả thông báo là đã đọc khi mở trang
        $user->unreadNotifications->markAsRead();

        return view('pages.notifications', compact('notifications'));
    }

    /**
     * Đánh dấu một thông báo là đã đọc và chuyển hướng đến URL hành động.
     */
    public function read(string $id): RedirectResponse
    {
        $user         = $this->currentUser();
        $notification = $user->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
            $url = $notification->data['action_url'] ?? '/dashboard';
            return redirect($url);
        }

        return redirect()->route('notifications.index');
    }

    /**
     * Đánh dấu tất cả thông báo là đã đọc (AJAX hoặc form POST).
     */
    public function markAllRead(Request $request): RedirectResponse|JsonResponse
    {
        $this->currentUser()->unreadNotifications->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Đã đánh dấu tất cả thông báo là đã đọc.');
    }

    /**
     * Xóa một thông báo.
     */
    public function destroy(string $id): RedirectResponse
    {
        $user         = $this->currentUser();
        $notification = $user->notifications()->find($id);

        if ($notification) {
            $notification->delete();
        }

        return back();
    }

    /**
     * Trả về số lượng thông báo chưa đọc (JSON — cho header badge).
     */
    public function unreadCount(): JsonResponse
    {
        return response()->json([
            'count' => $this->currentUser()->unreadNotifications()->count(),
        ]);
    }
}
