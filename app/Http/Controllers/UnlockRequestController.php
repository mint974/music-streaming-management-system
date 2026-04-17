<?php

namespace App\Http\Controllers;

use App\Models\AccountHistory;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Xử lý yêu cầu mở khóa tài khoản từ phía người dùng.
 * Route này không yêu cầu xác thực vì người dùng bị khóa bị đăng xuất.
 */
class UnlockRequestController extends Controller
{
    public function create(): View
    {
        return view('pages.unlock-request', [
            'cooldownEnds' => null,  // chỉ hiện khi submit email
        ]);
    }

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

        $user = User::where('email', $data['email'])->where('deleted', false)->first();

        if (! $user) {
            return back()->withInput()->withErrors(['email' => 'Không tìm thấy tài khoản với email này.']);
        }

        if ($user->status !== 'Bị khóa') {
            return back()->withInput()->withErrors(['email' => 'Tài khoản này không bị khóa, không cần gửi yêu cầu mở khóa.']);
        }

        // Kiểm tra đã có yêu cầu đang chờ xử lý chưa
        $existingPending = AccountHistory::unlockRequests()
            ->where('user_id', $user->id)
            ->where('unlock_status', 'pending')
            ->first();

        if ($existingPending) {
            return back()->withInput()->withErrors(['email' => 'Bạn đã gửi yêu cầu khôi phục rồi. Vui lòng chờ quản trị viên duyệt.']);
        }

        // Kiểm tra thời gian chờ 1 ngày sau khi bị từ chối
        $lastRejected = AccountHistory::unlockRequests()
            ->where('user_id', $user->id)
            ->where('unlock_status', 'rejected')
            ->whereNotNull('handled_at')
            ->latest('handled_at')
            ->first();

        if ($lastRejected) {
            $canRequestAt = $lastRejected->handled_at->addDay();
            if ($canRequestAt->isFuture()) {
                return back()->withInput()->withErrors([
                    'email' => 'Yêu cầu mở khóa trước đó đã bị từ chối. Bạn có thể gửi lại sau '
                              . $canRequestAt->format('H:i, d/m/Y') . '.',
                ]);
            }
        }

        AccountHistory::create([
            'type'          => 'unlock_request',
            'action'        => 'Gửi yêu cầu mở khóa tài khoản',
            'status'        => 'Đang yêu cầu khôi phục',
            'content'       => $data['content'],
            'unlock_status' => 'pending',
            'user_id'       => $user->id,
            'created_by'    => $user->id,
        ]);

        return redirect()->route('unlock-request.sent')
            ->with('unlock_email', $data['email']);
    }

    public function sent(): View
    {
        return view('pages.unlock-request-sent');
    }
}
