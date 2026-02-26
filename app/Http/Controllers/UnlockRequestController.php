<?php

namespace App\Http\Controllers;

use App\Models\UnlockRequest;
use App\Models\User;
use App\Notifications\AccountUpdated;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Xử lý yêu cầu mở khóa tài khoản từ phía người dùng.
 * Route này không yêu cầu xác thực vì người dùng bị khóa bị đăng xuất.
 */
class UnlockRequestController extends Controller
{
    /**
     * Hiển thị form gửi yêu cầu mở khóa.
     */
    public function create(): View
    {
        return view('pages.unlock-request');
    }

    /**
     * Lưu yêu cầu mở khóa.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email'   => ['required', 'email', 'max:255'],
            'content' => ['required', 'string', 'min:30', 'max:2000'],
        ], [
            'email.required'   => 'Vui lòng nhập địa chỉ email tài khoản bị khóa.',
            'email.email'      => 'Địa chỉ email không hợp lệ.',
            'content.required' => 'Vui lòng nhập nội dung khiếu nại.',
            'content.min'      => 'Nội dung khiếu nại cần ít nhất 30 ký tự để mô tả chi tiết.',
            'content.max'      => 'Nội dung không được vượt quá 2000 ký tự.',
        ]);

        // Tìm user theo email
        $user = User::where('email', $data['email'])->where('deleted', false)->first();

        if (! $user) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'Không tìm thấy tài khoản với email này.']);
        }

        if ($user->status !== 'Bị khóa') {
            return back()
                ->withInput()
                ->withErrors(['email' => 'Tài khoản này không bị khóa, không cần gửi yêu cầu mở khóa.']);
        }

        // Kiểm tra đã có yêu cầu đang chờ xử lý chưa
        $existingPending = UnlockRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($existingPending) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'Tài khoản này đã có yêu cầu mở khóa đang chờ xử lý. Vui lòng chờ admin phản hồi.']);
        }

        // Tạo yêu cầu mở khóa
        UnlockRequest::create([
            'user_id' => $user->id,
            'content' => $data['content'],
            'status'  => 'pending',
        ]);

        return redirect()->route('unlock-request.sent')
            ->with('unlock_email', $data['email']);
    }

    /**
     * Trang xác nhận đã gửi yêu cầu.
     */
    public function sent(): View
    {
        return view('pages.unlock-request-sent');
    }
}
