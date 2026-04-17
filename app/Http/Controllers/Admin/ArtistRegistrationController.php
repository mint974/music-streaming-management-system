<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArtistProfile;
use App\Models\ArtistRegistration;
use App\Notifications\ArtistProfileCompletionRequired;
use App\Notifications\ArtistRegistrationReviewed;
use App\Notifications\RefundIssued;
use App\Repositories\UserRepository;
use App\Services\ArtistRegistrationStateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ArtistRegistrationController extends Controller
{
    public function __construct(
        protected UserRepository $repo,
        protected ArtistRegistrationStateService $stateService
    ) {}

    /**
     * Danh sách đơn đăng ký nghệ sĩ.
     * GET /admin/artist-registrations
     */
    public function index(Request $request): \Illuminate\View\View|RedirectResponse
    {
        $tab          = $request->input('tab', 'pending_review');
        $refundFilter = $request->input('refund_filter'); // pending | completed | none | null (all)
        $search       = $request->input('search');
        $dateFrom     = $request->input('date_from');
        $dateTo       = $request->input('date_to');

        if ($dateFrom && $dateTo && $dateFrom > $dateTo) {
            return redirect()->route('admin.artist-registrations.index', [
                'tab' => $tab,
                'refund_filter' => $refundFilter,
                'search' => $search,
            ])->with('error', 'Ngày bắt đầu không thể lớn hơn ngày kết thúc.');
        }

        $query = ArtistRegistration::with(['user.socialLinks', 'package', 'reviewer', 'payment'])
            ->when($tab !== 'all', fn ($q) => $q->where('status', $tab))
            ->when($search, function ($q) use ($search) {
                $q->where(function($q2) use ($search) {
                    $q2->where('submitted_stage_name', 'like', "%{$search}%")
                       ->orWhereHas('user', function($qu) use ($search) {
                           $qu->where('name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%");
                       });
                });
            })
            ->when($dateFrom, function ($q) use ($dateFrom) {
                $q->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($dateTo, function ($q) use ($dateTo) {
                $q->whereDate('created_at', '<=', $dateTo);
            });

        // Lọc hoàn tiền — chỉ áp dụng ở tab "rejected"
        if ($tab === 'rejected' && $refundFilter !== null) {
            if ($refundFilter === 'none') {
                $query->where(function ($q) {
                    $q->whereDoesntHave('payment')
                        ->orWhereHas('payment', fn ($paymentQuery) => $paymentQuery->whereNull('refund_amount'));
                });
            } elseif ($refundFilter === 'pending') {
                $query->whereHas('payment', function ($paymentQuery) {
                    $paymentQuery->whereNotNull('refund_amount')
                        ->whereNull('refunded_at');
                });
            } elseif ($refundFilter === 'completed') {
                $query->whereHas('payment', function ($paymentQuery) {
                    $paymentQuery->whereNotNull('refund_amount')
                        ->whereNotNull('refunded_at');
                });
            } else {
                $query->whereRaw('1 = 0');
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
            'pending'   => ArtistRegistration::where('status', 'rejected')
                ->whereHas('payment', fn ($paymentQuery) => $paymentQuery->whereNotNull('refund_amount')->whereNull('refunded_at'))
                ->count(),
            'completed' => ArtistRegistration::where('status', 'rejected')
                ->whereHas('payment', fn ($paymentQuery) => $paymentQuery->whereNotNull('refund_amount')->whereNotNull('refunded_at'))
                ->count(),
            'none'      => ArtistRegistration::where('status', 'rejected')
                ->where(function ($q) {
                    $q->whereDoesntHave('payment')
                        ->orWhereHas('payment', fn ($paymentQuery) => $paymentQuery->whereNull('refund_amount'));
                })
                ->count(),
        ];

        return view('admin.artist-registrations.index', compact(
            'registrations', 'counts', 'refundCounts', 'tab', 'refundFilter',
            'search', 'dateFrom', 'dateTo'
        ));
    }

    /**
     * Phê duyệt đơn → nâng cấp role user lên 'artist'.
     * POST /admin/artist-registrations/{id}/approve
     */
    public function approve(Request $request, int $id): RedirectResponse
    {
        $registration = ArtistRegistration::with('user', 'package', 'payment')->findOrFail($id);

        if (!$registration->isPendingReview()) {
            return back()->with('error', 'Đơn này không ở trạng thái chờ xét duyệt.');
        }

        if (! $registration->user->isArtistProfileCompleteForRegistration()) {
            $missing = implode(', ', $registration->user->missingArtistProfileFieldsForRegistration());
            return back()->with('error', 'Hồ sơ nghệ sĩ chưa đầy đủ, chưa thể phê duyệt. Thiếu: ' . $missing);
        }

        $request->validate([
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        $admin = Auth::guard('admin')->user();

        // Nâng cấp role → artist + ghi lịch sử (chỉ khi chưa là artist)
        if (! $registration->user->isArtist()) {
            $this->repo->adminChangeRole($registration->user, 'artist', $admin->id);
        }

        ArtistProfile::updateOrCreate(
            ['user_id' => $registration->user->id],
            [
                'artist_package_id' => $registration->package_id,
                'stage_name'        => $registration->artist_name,
                'bio'               => $registration->user->bio,
                'avatar'            => $registration->user->artistProfile?->avatar ?? $registration->user->avatar,
                'cover_image'       => $registration->user->artistProfile?->cover_image ?? $registration->user->cover_image,
                'verified_at'       => $registration->user->artist_verified_at,
                'status'            => \App\Models\ArtistProfile::STATUS_ACTIVE,
                'revoked_at'        => $registration->user->artist_revoked_at,
                'start_date'        => now(),
                'end_date'          => now()->addDays((int) ($registration->package->duration_days ?? 365)),
            ]
        );

        // Cập nhật đơn đăng ký với thời hạn dựa trên gói
        $durationDays = $registration->package->duration_days ?? 365;
        try {
            $this->stateService->approve(
                $registration,
                (int) $admin->id,
                $request->input('admin_note'),
                (int) $durationDays
            );
        } catch (\Throwable $e) {
            return back()->with('error', 'Không thể phê duyệt: ' . $e->getMessage());
        }

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
        $registration = ArtistRegistration::with('user', 'package', 'payment')->findOrFail($id);

        if (!$registration->isPendingReview()) {
            return back()->with('error', 'Đơn này không ở trạng thái chờ xét duyệt.');
        }

        $request->validate([
            'admin_note' => ['required', 'string', 'min:10', 'max:500'],
            'rejection_reason_code' => ['required', 'in:' . implode(',', array_keys(ArtistRegistration::rejectionReasonOptions()))],
        ], [
            'admin_note.required' => 'Vui lòng nhập lý do từ chối.',
            'admin_note.min'      => 'Lý do từ chối phải có ít nhất 10 ký tự.',
            'rejection_reason_code.required' => 'Vui lòng chọn nhóm lý do từ chối.',
            'rejection_reason_code.in' => 'Mã lý do từ chối không hợp lệ.',
        ]);

        $admin = Auth::guard('admin')->user();

        // Ghi nhận hoàn tiền toàn bộ nếu user đã thanh toán
        $payment = $registration->payment;
        $refundAmount = null;
        if ($payment && $payment->isPaid()) {
            $refundAmount = (int) ($registration->package?->price ?? 0);
        }

        try {
            $this->stateService->reject(
                $registration,
                (int) $admin->id,
                (string) $request->input('admin_note'),
                (string) $request->input('rejection_reason_code')
            );

            if ($payment && $refundAmount !== null && $refundAmount > 0) {
                $payment->update([
                    'refund_amount' => $refundAmount,
                    'refunded_at' => null,
                ]);
            }
        } catch (\Throwable $e) {
            return back()->with('error', 'Không thể từ chối: ' . $e->getMessage());
        }

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
        $registration = ArtistRegistration::with(['user', 'payment'])->findOrFail($id);

        if (!$registration->isRefundPending()) {
            return back()->with('error', 'Đơn này không ở trạng thái chờ hoàn tiền.');
        }

        $admin = Auth::guard('admin')->user();

        try {
            $this->stateService->confirmRefund($registration, (int) $admin->id);
        } catch (\Throwable $e) {
            return back()->with('error', 'Không thể xác nhận hoàn tiền: ' . $e->getMessage());
        }

        $refundAmount = (int) ($registration->payment?->refund_amount ?? 0);

        $this->repo->createHistory(
            $registration->user->id,
            $admin->id,
            '[Admin] Xác nhận hoàn tiền ' . number_format($refundAmount) . ' ₫ — ' . $registration->artist_name,
            $registration->user->status
        );

        try {
            $registration->user->notify(new RefundIssued(
                $refundAmount,
                'artist_rejected',
                $registration->payment?->transaction_code ?? ''
            ));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send refund confirmed notification: ' . $e->getMessage());
        }

        return back()->with('success', 'Đã xác nhận hoàn tiền <strong>' . number_format($refundAmount) . ' ₫</strong> cho <strong>' . $registration->user->name . '</strong>.');
    }

    /**
     * Yêu cầu user bổ sung hồ sơ nghệ sĩ còn thiếu trước khi xét duyệt tiếp.
     * POST /admin/artist-registrations/{id}/request-profile-completion
     */
    public function requestProfileCompletion(Request $request, int $id): RedirectResponse
    {
        $registration = ArtistRegistration::with(['user.socialLinks', 'package'])->findOrFail($id);

        if (! $registration->isPendingReview()) {
            return back()->with('error', 'Chỉ có thể yêu cầu bổ sung thông tin với đơn đang chờ xét duyệt.');
        }

        $request->validate([
            'admin_note' => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'admin_note.required' => 'Vui lòng nhập nội dung yêu cầu bổ sung thông tin.',
            'admin_note.min' => 'Nội dung yêu cầu phải có ít nhất 10 ký tự.',
        ]);

        $missingFields = $registration->user->missingArtistProfileFieldsForRegistration();

        if (empty($missingFields)) {
            return back()->with('info', 'Hồ sơ của user đã đầy đủ thông tin. Bạn có thể tiến hành phê duyệt.');
        }

        $admin = Auth::guard('admin')->user();

        $this->repo->createHistory(
            $registration->user->id,
            $admin->id,
            '[Admin] Yêu cầu bổ sung hồ sơ Nghệ sĩ — ' . $registration->artist_name,
            $registration->user->status
        );

        try {
            $registration->user->notify(new ArtistProfileCompletionRequired(
                $registration,
                (string) $request->input('admin_note'),
                $missingFields
            ));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send profile completion required notification: ' . $e->getMessage());
            return back()->with('error', 'Không thể gửi thông báo yêu cầu bổ sung hồ sơ. Vui lòng thử lại.');
        }

        return back()->with('success', 'Đã gửi yêu cầu bổ sung hồ sơ cho <strong>' . $registration->user->name . '</strong>.');
    }
}
