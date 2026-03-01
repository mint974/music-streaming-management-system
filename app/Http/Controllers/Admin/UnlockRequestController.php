<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountHistory;
use App\Notifications\AccountUpdated;
use App\Repositories\UserRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UnlockRequestController extends Controller
{
    public function __construct(protected UserRepository $repo) {}

    /**
     * Danh sách yêu cầu mở khóa.
     */
    public function index(Request $request): View
    {
        $status = $request->get('status', 'pending');

        $requests = AccountHistory::with(['user:id,name,email,avatar,status,lock_reason'])
            ->unlockRequests()
            ->when($status !== 'all', fn ($q) => $q->where('unlock_status', $status))
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'pending'  => AccountHistory::unlockRequests()->where('unlock_status', 'pending')->count(),
            'approved' => AccountHistory::unlockRequests()->where('unlock_status', 'approved')->count(),
            'rejected' => AccountHistory::unlockRequests()->where('unlock_status', 'rejected')->count(),
        ];

        return view('admin.unlock-requests.index', compact('requests', 'status', 'counts'));
    }

    /**
     * Chấp thuận yêu cầu mở khóa — tự động mở khóa tài khoản.
     */
    public function approve(Request $request, int $id): RedirectResponse
    {
        $request->validate(
            ['admin_note' => ['nullable', 'string', 'max:500']],
            ['admin_note.max' => 'Ghi chú không được quá 500 ký tự.']
        );

        $unlockReq = AccountHistory::unlockRequests()->with('user')->findOrFail($id);

        if (! $unlockReq->isPending()) {
            return back()->with('error', 'Yêu cầu này đã được xử lý trước đó.');
        }

        $admin = Auth::guard('admin')->user();
        $user  = $unlockReq->user;

        $unlockReq->update([
            'unlock_status' => 'approved',
            'admin_note'    => $request->admin_note,
            'handled_by'    => $admin->id,
            'handled_at'    => now(),
        ]);

        // Mở khóa tài khoản nếu đang bị khóa
        if ($user->status === 'Bị khóa') {
            $this->repo->adminToggleStatus($user, $admin->id, null);
        }

        $this->repo->createHistory(
            $user->id,
            $admin->id,
            '[Admin] Chấp thuận yêu cầu mở khóa',
            'Đang hoạt động'
        );

        $user->notify(new AccountUpdated('unlock_approved', $request->admin_note));

        return back()->with('success', "Đã chấp thuận yêu cầu và mở khóa tài khoản <strong>{$user->name}</strong>.");
    }

    /**
     * Từ chối yêu cầu mở khóa.
     */
    public function reject(Request $request, int $id): RedirectResponse
    {
        $request->validate(
            ['admin_note' => ['required', 'string', 'max:500']],
            ['admin_note.required' => 'Vui lòng nhập lý do từ chối để thông báo cho người dùng.']
        );

        $unlockReq = AccountHistory::unlockRequests()->with('user')->findOrFail($id);

        if (! $unlockReq->isPending()) {
            return back()->with('error', 'Yêu cầu này đã được xử lý trước đó.');
        }

        $admin = Auth::guard('admin')->user();
        $user  = $unlockReq->user;

        $unlockReq->update([
            'unlock_status' => 'rejected',
            'admin_note'    => $request->admin_note,
            'handled_by'    => $admin->id,
            'handled_at'    => now(),
        ]);

        $this->repo->createHistory(
            $user->id,
            $admin->id,
            '[Admin] Từ chối yêu cầu mở khóa: ' . $request->admin_note,
            $user->status
        );

        $user->notify(new AccountUpdated('unlock_rejected', $request->admin_note));

        return back()->with('success', "Đã từ chối yêu cầu mở khóa của <strong>{$user->name}</strong>.");
    }
}


    /**
     * Danh sách yêu cầu mở khóa.
     */
    public function index(Request $request): View
    {
        $status = $request->get('status', 'pending');

        $requests = UnlockRequest::with(['user:id,name,email,avatar,status,lock_reason'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'pending'  => UnlockRequest::where('status', 'pending')->count(),
            'approved' => UnlockRequest::where('status', 'approved')->count(),
            'rejected' => UnlockRequest::where('status', 'rejected')->count(),
        ];

        return view('admin.unlock-requests.index', compact('requests', 'status', 'counts'));
    }

    /**
     * Chấp thuận yêu cầu mở khóa — tự động mở khóa tài khoản.
     */
    public function approve(Request $request, int $id): RedirectResponse
    {
        $request->validate(
            ['admin_note' => ['nullable', 'string', 'max:500']],
            ['admin_note.max' => 'Ghi chú không được quá 500 ký tự.']
        );

        $unlockReq = UnlockRequest::with('user')->findOrFail($id);

        if (! $unlockReq->isPending()) {
            return back()->with('error', 'Yêu cầu này đã được xử lý trước đó.');
        }

        $admin = Auth::guard('admin')->user();
        $user  = $unlockReq->user;

        // Cập nhật yêu cầu
        $unlockReq->update([
            'status'     => 'approved',
            'admin_note' => $request->admin_note,
            'handled_by' => $admin->id,
            'handled_at' => now(),
        ]);

        // Mở khóa tài khoản nếu đang bị khóa
        if ($user->status === 'Bị khóa') {
            $this->repo->adminToggleStatus($user, $admin->id, null);
            // adminToggleStatus đã gửi notify 'status_unlocked', ta ghi đè với unlock_approved
        }

        // Ghi lịch sử thêm
        $this->repo->createHistory(
            $user->id,
            $admin->id,
            '[Admin] Chấp thuận yêu cầu mở khóa',
            'Đang hoạt động'
        );

        // Notify user - ghi đè status_unlocked bằng unlock_approved
        $user->notify(new AccountUpdated('unlock_approved', $request->admin_note));

        return back()->with('success', "Đã chấp thuận yêu cầu và mở khóa tài khoản <strong>{$user->name}</strong>.");
    }

    /**
     * Từ chối yêu cầu mở khóa.
     */
    public function reject(Request $request, int $id): RedirectResponse
    {
        $request->validate(
            ['admin_note' => ['required', 'string', 'max:500']],
            ['admin_note.required' => 'Vui lòng nhập lý do từ chối để thông báo cho người dùng.']
        );

        $unlockReq = UnlockRequest::with('user')->findOrFail($id);

        if (! $unlockReq->isPending()) {
            return back()->with('error', 'Yêu cầu này đã được xử lý trước đó.');
        }

        $admin = Auth::guard('admin')->user();
        $user  = $unlockReq->user;

        $unlockReq->update([
            'status'     => 'rejected',
            'admin_note' => $request->admin_note,
            'handled_by' => $admin->id,
            'handled_at' => now(),
        ]);

        $this->repo->createHistory(
            $user->id,
            $admin->id,
            '[Admin] Từ chối yêu cầu mở khóa: ' . $request->admin_note,
            $user->status
        );

        // Gửi email thông báo từ chối kèm lý do
        $user->notify(new AccountUpdated('unlock_rejected', $request->admin_note));

        return back()->with('success', "Đã từ chối yêu cầu mở khóa của <strong>{$user->name}</strong>.");
    }
}
