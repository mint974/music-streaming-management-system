<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArtistRegistration;
use App\Notifications\ArtistRegistrationReviewed;
use App\Notifications\RefundIssued;
use App\Repositories\UserRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ArtistRegistrationController extends Controller
{
    public function __construct(protected UserRepository $repo) {}

    /**
     * Danh sách đơn đăng ký nghệ sĩ.
     * GET /admin/artist-registrations
     */
    public function index(Request $request): View
    {
        $tab          = $request->input('tab', 'pending_review');
        $refundFilter = $request->input('refund_filter'); // pending | completed | none | null (all)

        $query = ArtistRegistration::with(['user', 'package', 'reviewer'])
            ->when($tab !== 'all', fn ($q) => $q->where('status', $tab));

        // Lọc hoàn tiền — chỉ áp dụng ở tab "rejected"
        if ($tab === 'rejected' && $refundFilter !== null) {
            if ($refundFilter === 'none') {
                $query->whereNull('refund_status');
            } else {
                $query->where('refund_status', $refundFilter);
            }
        }

        $registrations = $query->latest()->paginate(15)->withQueryString();

        $counts = [
            'all'            => ArtistRegistration::count(),
            'pending_payment'=> ArtistRegistration::where('status', 'pending_payment')->count(),
            'pending_review' => ArtistRegistration::where('status', 'pending_review')->count(),
            'approved'       => ArtistRegistration::where('status', 'approved')->count(),
            'rejected'       => ArtistRegistration::where('status', 'rejected')->count(),
        ];

        // Sub-counts hoàn tiền cho tab rejected
        $refundCounts = [
            'pending'   => ArtistRegistration::where('status', 'rejected')->where('refund_status', 'pending')->count(),
            'completed' => ArtistRegistration::where('status', 'rejected')->where('refund_status', 'completed')->count(),
            'none'      => ArtistRegistration::where('status', 'rejected')->whereNull('refund_status')->count(),
        ];

        return view('admin.artist-registrations.index', compact('registrations', 'counts', 'refundCounts', 'tab', 'refundFilter'));
    }

    /**
     * Phê duyệt đơn → nâng cấp role user lên 'artist'.
     * POST /admin/artist-registrations/{id}/approve
     */
    public function approve(Request $request, int $id): RedirectResponse
    {
        $registration = ArtistRegistration::with('user', 'package')->findOrFail($id);

        if (!$registration->isPendingReview()) {
            return back()->with('error', 'Đơn này không ở trạng thái chờ xét duyệt.');
        }

        $request->validate([
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        $admin = Auth::guard('admin')->user();

        // Nâng cấp role → artist + ghi lịch sử (chỉ khi chưa là artist)
        if (! $registration->user->isArtist()) {
            $this->repo->adminChangeRole($registration->user, 'artist', $admin->id);
        }

        // Đồng bộ nghệ danh từ đơn đăng ký vào hồ sơ user.
        if ($registration->user->artist_name !== $registration->artist_name) {
            $registration->user->update([
                'artist_name' => $registration->artist_name,
            ]);
        }

        // Cập nhật đơn đăng ký với thời hạn dựa trên gói
        $durationDays = $registration->package->duration_days ?? 365;
        $registration->update([
            'status'      => 'approved',
            'admin_note'  => $request->input('admin_note'),
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
            'expires_at'  => now()->addDays($durationDays),
        ]);

        // Ghi lịch sử tài khoản riêng cho hành động phê duyệt
        $this->repo->createHistory(
            $registration->user->id,
            $admin->id,
            '[Admin] Phê duyệt đăng ký Nghệ sĩ — ' . $registration->artist_name,
            $registration->user->status
        );

        // Thông báo đến user (db + mail)
        try {
            $registration->user->notify(new ArtistRegistrationReviewed($registration));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send artist approved notification: ' . $e->getMessage());
        }

        return back()->with('success', "Đã phê duyệt đơn đăng ký nghệ sĩ của <strong>{$registration->user->name}</strong> (nghệ danh: {$registration->artist_name}).");
    }

    /**
     * Từ chối đơn đăng ký (lý do bắt buộc).
     * POST /admin/artist-registrations/{id}/reject
     */
    public function reject(Request $request, int $id): RedirectResponse
    {
        $registration = ArtistRegistration::with('user', 'package')->findOrFail($id);

        if (!$registration->isPendingReview()) {
            return back()->with('error', 'Đơn này không ở trạng thái chờ xét duyệt.');
        }

        $request->validate([
            'admin_note' => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'admin_note.required' => 'Vui lòng nhập lý do từ chối.',
            'admin_note.min'      => 'Lý do từ chối phải có ít nhất 10 ký tự.',
        ]);

        $admin = Auth::guard('admin')->user();

        // Ghi nhận hoàn tiền toàn bộ nếu user đã thanh toán
        $refundAmount = $registration->amount_paid > 0 ? $registration->amount_paid : null;

        $registration->update([
            'status'        => 'rejected',
            'admin_note'    => $request->input('admin_note'),
            'reviewed_by'   => $admin->id,
            'reviewed_at'   => now(),
            'refund_amount' => $refundAmount,
            'refunded_at'   => null,  // được set sau khi VNPAY xác nhận
            'refund_status' => $refundAmount ? 'pending' : null,
        ]);

        // Ghi lịch sử tài khoản
        $this->repo->createHistory(
            $registration->user->id,
            $admin->id,
            '[Admin] Từ chối đăng ký Nghệ sĩ — ' . $registration->artist_name,
            $registration->user->status
        );

        // Thông báo đến user (db + mail) — bao gồm thông tin hoàn tiền
        try {
            $registration->user->notify(new ArtistRegistrationReviewed($registration));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send artist rejected notification: ' . $e->getMessage());
        }

        $refundMsg = $refundAmount
            ? ' Số tiền <strong>' . number_format($refundAmount) . ' ₫</strong> sẽ được hoàn lại cho user.'
            : '';

        return back()->with('success', "Đã từ chối đơn đăng ký của <strong>{$registration->user->name}</strong>.{$refundMsg}");
    }

    /**
     * Gọi VNPAY Refund API và xác nhận hoàn tiền.
     * POST /admin/artist-registrations/{id}/confirm-refund
     */
    public function confirmRefund(int $id): RedirectResponse
    {
        $registration = ArtistRegistration::with('user')->findOrFail($id);

        if (!$registration->isRefundPending()) {
            return back()->with('error', 'Đơn này không ở trạng thái chờ hoàn tiền.');
        }

        $admin = Auth::guard('admin')->user();

        $registration->update([
            'refund_status'       => 'completed',
            'refunded_at'         => now(),
            'refund_confirmed_by' => $admin->id,
            'refund_confirmed_at' => now(),
        ]);

        $this->repo->createHistory(
            $registration->user->id,
            $admin->id,
            '[Admin] Xác nhận hoàn tiền ' . number_format($registration->refund_amount) . ' ₫ — ' . $registration->artist_name,
            $registration->user->status
        );

        try {
            $registration->user->notify(new RefundIssued(
                $registration->refund_amount,
                'artist_rejected',
                $registration->transaction_code ?? ''
            ));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send refund confirmed notification: ' . $e->getMessage());
        }

        return back()->with('success', 'Đã xác nhận hoàn tiền <strong>' . number_format($registration->refund_amount) . ' ₫</strong> cho <strong>' . $registration->user->name . '</strong>.');
    }
}
