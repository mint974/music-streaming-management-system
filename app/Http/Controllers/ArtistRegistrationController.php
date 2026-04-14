<?php

namespace App\Http\Controllers;

use App\Mail\ArtistRegistrationPayment;
use App\Models\ArtistPackage;
use App\Models\Payment;
use App\Models\ArtistRegistration;
use App\Models\User;
use App\Notifications\NewArtistRegistration;
use App\Services\ArtistRegistrationStateService;
use App\Services\VnpayPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ArtistRegistrationController extends Controller
{
    public function __construct(
        private readonly VnpayPaymentService $vnpay,
        private readonly ArtistRegistrationStateService $stateService
    ) {}

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function currentUser(): User
    {
        /** @var User $user */
        $user = Auth::user();
        return $user;
    }

    private function getIpAddress(): string
    {
        return $this->vnpay->getClientIp();
    }

    private function buildPaymentTitle(string $prefix, string $title): string
    {
        return $this->vnpay->buildPaymentTitle($prefix, $title);
    }

    private function clearStalePendingPayment(User $user): void
    {
        ArtistRegistration::where('user_id', $user->id)
            ->where('status', 'pending_payment')
            ->where('created_at', '<', now()->subMinutes(30))
            ->delete();
    }

    /**
     * Ghép query string và chuỗi hash theo đúng thứ tự VNPAY yêu cầu.
     */
    private function buildVnpayQueryData(array $inputData): array
    {
        return $this->vnpay->buildQueryAndHashData($inputData);
    }

    private function buildVnpayUrl(string $returnUrl, string $txnRef, string $orderInfo, int $amount): string
    {
        return $this->vnpay->buildVnpayUrl($returnUrl, $txnRef, $orderInfo, $amount);
    }

    // ─── Actions ──────────────────────────────────────────────────────────────

    /**
     * Trang giới thiệu gói đăng ký nghệ sĩ.
     * GET /artist-register
     */
    public function index(): View|RedirectResponse
    {
        $user = $this->currentUser();

        $this->clearStalePendingPayment($user);

        // Bị thu hồi vĩnh viễn — không thể đăng ký lại
        if ($user->isArtistRevoked()) {
            return redirect()->route('dashboard')
                ->with('error', 'Quyền Nghệ sĩ của bạn đã bị thu hồi vĩnh viễn. Bạn không thể đăng ký lại gói Nghệ sĩ.');
        }

        // Đang là nghệ sĩ và gói còn hiệu lực — về trang nghệ sĩ
        if ($user->isArtist() && !$user->isArtistPackageExpired()) {
            return redirect()->route('artist.dashboard')
                ->with('info', 'Bạn đã là Nghệ sĩ với gói đang hoạt động.');
        }

        // Kiểm tra thời gian chờ sau khi bị từ chối (3 ngày)
        $cooldownEnds = $user->artistReapplyCooldownEnds();

        // Kiểm tra đơn đang chờ xử lý
        $pending = ArtistRegistration::where('user_id', $user->id)
            ->whereIn('status', ['pending_payment', 'pending_review'])
            ->latest()
            ->first();

        $packages = ArtistPackage::active()->orderBy('price')->get();

        // Thông tin gói cuối cùng (để hiển thị cảnh báo hết hạn)
        $expiredRegistration = null;
        if ($user->isArtist() && $user->isArtistPackageExpired()) {
            $expiredRegistration = $user->artistRegistrations()
                ->where('status', 'approved')
                ->latest('expires_at')
                ->first();
        }

        // Lịch sử đăng ký nghệ sĩ (tất cả đơn đã thanh toán)
        $registrationHistory = ArtistRegistration::with('package')
            ->with('payment')
            ->where('user_id', $user->id)
            ->whereNotIn('status', ['pending_payment'])
            ->latest()
            ->get();

        $latestRejected = $registrationHistory
            ->first(fn (ArtistRegistration $reg) => $reg->isRejected());

        $pendingRequiresProfileCompletion = false;
        $pendingMissingProfileFields = [];

        if ($pending && $pending->isPendingReview()) {
            $pendingRequiresProfileCompletion = ! $user->isArtistProfileCompleteForRegistration();
            $pendingMissingProfileFields = $pendingRequiresProfileCompletion
                ? $user->missingArtistProfileFieldsForRegistration()
                : [];
        }

        return view('pages.artist-register', compact(
            'packages',
            'pending',
            'expiredRegistration',
            'registrationHistory',
            'cooldownEnds',
            'latestRejected',
            'pendingRequiresProfileCompletion',
            'pendingMissingProfileFields'
        ));
    }

    /**
     * Hiển thị form đăng ký chi tiết cho gói đã chọn.
     * GET /artist-register/{packageId}
     */
    public function create(int $packageId): View|RedirectResponse
    {
        $user    = $this->currentUser();
        $package = ArtistPackage::active()->findOrFail($packageId);

        $this->clearStalePendingPayment($user);

        // Bị thu hồi vĩnh viễn
        if ($user->isArtistRevoked()) {
            return redirect()->route('dashboard')
                ->with('error', 'Quyền Nghệ sĩ của bạn đã bị thu hồi vĩnh viễn.');
        }

        if ($user->isArtist() && !$user->isArtistPackageExpired()) {
            return redirect()->route('artist.dashboard')
                ->with('info', 'Bạn đã là Nghệ sĩ trên Blue Wave Music.');
        }

        if ($user->hasPendingArtistRegistration()) {
            return redirect()->route('artist-register.index')
                ->with('warning', 'Bạn đã có đơn đăng ký đang được xử lý.');
        }

        if ($user->isArtistReapplyCooldown()) {
            $until = $user->artistReapplyCooldownEnds()->format('d/m/Y H:i');
            return redirect()->route('artist-register.index')
                ->with('error', "Đơn đăng ký của bạn vừa bị từ chối. Bạn có thể đăng ký lại sau {$until}.");
        }

        return view('pages.artist-register-form', compact('package', 'user'));
    }

    /**
     * Lưu đơn đăng ký và chuyển đến VNPAY.
     * POST /artist-register/{packageId}
     */
    public function checkout(Request $request, int $packageId): RedirectResponse
    {
        $user    = $this->currentUser();
        $package = ArtistPackage::active()->findOrFail($packageId);
        $isUpgradeFlow = $request->boolean('upgrade');
        $activeRegistration = $user->activeArtistRegistration();

        $this->clearStalePendingPayment($user);

        // Bị thu hồi vĩnh viễn
        if ($user->isArtistRevoked()) {
            return redirect()->route('dashboard')
                ->with('error', 'Quyền Nghệ sĩ của bạn đã bị thu hồi vĩnh viễn.');
        }

        if ($activeRegistration && ! $isUpgradeFlow) {
            return redirect()->route('artist.dashboard')
                ->with('info', 'Bạn đang có gói nghệ sĩ còn hiệu lực.');
        }

        if ($activeRegistration && $isUpgradeFlow) {
            if ((int) $activeRegistration->package_id === (int) $package->id) {
                return back()->with('error', 'Bạn đang sử dụng gói này rồi. Vui lòng chọn gói khác.');
            }

            $currentPrice = (int) optional($activeRegistration->package)->price;
            if ((int) $package->price <= $currentPrice) {
                return back()->with('error', 'Chỉ hỗ trợ nâng cấp lên gói có giá cao hơn gói hiện tại.');
            }
        }

        if ($user->hasPendingArtistRegistration()) {
            return redirect()->route('artist-register.index')
                ->with('warning', 'Bạn đã có đơn đăng ký đang được xử lý.');
        }

        if ($user->isArtistReapplyCooldown()) {
            $until = $user->artistReapplyCooldownEnds()->format('d/m/Y H:i');
            return redirect()->route('artist-register.index')
                ->with('error', "Đơn đăng ký của bạn vừa bị từ chối. Bạn có thể đăng ký lại sau {$until}.");
        }

        $validationRules = [
            'artist_name' => ['required', 'string', 'max:100', 'min:2'],
            'bio'         => ['nullable', 'string', 'max:1000'],
        ];

        if (! $isUpgradeFlow) {
            $validationRules['accept_terms'] = ['accepted'];
        }

        $request->validate($validationRules, [
            'artist_name.required' => 'Vui lòng nhập tên nghệ danh.',
            'artist_name.min'      => 'Tên nghệ danh phải có ít nhất 2 ký tự.',
            'artist_name.max'      => 'Tên nghệ danh không được vượt quá 100 ký tự.',
            'accept_terms.accepted' => 'Bạn cần đồng ý điều khoản dành cho Nghệ sĩ trước khi thanh toán.',
        ]);

        DB::beginTransaction();
        try {
            // Hủy các đơn pending_payment cũ (mắc kẹt)
            ArtistRegistration::where('user_id', $user->id)
                ->where('status', 'pending_payment')
                ->delete();

            $txnRef = 'ART_' . $user->id . '_' . time();

            $registration = ArtistRegistration::create([
                'user_id'               => $user->id,
                'package_id'            => $package->id,
                'submitted_stage_name'  => $request->input('artist_name'),
                'submitted_avt'         => $user->artistProfile?->avatar ?? $user->avatar,
                'submitted_cover_image' => $user->artistProfile?->cover_image,
                'status'                => ArtistRegistration::STATUS_PENDING_PAYMENT,
            ]);

            $registration->payment()->create([
                'user_id'                   => $user->id,
                'provider'                  => 'VNPAY',
                'method'                    => 'VNPAY',
                'amount'                    => $package->price,
                'status'                    => 'pending',
                'transaction_code'          => $txnRef,
                'provider_transaction_no'   => null,
                'provider_pay_date'         => null,
                'paid_at'                   => null,
                'raw_response'              => null,
            ]);

            DB::commit();

            $returnUrl = route('artist-register.vnpay.return');
            $orderInfo = $this->buildPaymentTitle('Dang ky goi Nghe Si', $package->name . ' tai Blue Wave Music');
            \Illuminate\Support\Facades\Log::info('[VNPAY - Artist] Bắt đầu khởi tạo giao dịch: ', ['txnRef' => $txnRef, 'amount' => $package->price, 'user_id' => $user->id]);
            $vnpayUrl  = $this->buildVnpayUrl($returnUrl, $txnRef, $orderInfo, $package->price);
            \Illuminate\Support\Facades\Log::info('[VNPAY - Artist] URL VNPAY đã sinh: ' . $vnpayUrl);

            return redirect()->away($vnpayUrl);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[VNPAY - Artist] Checkout error: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi khởi tạo thanh toán. Vui lòng thử lại.');
        }
    }

    /**
     * Tiếp tục thanh toán cho đơn pending_payment đã có.
     */
    public function payPending(int $id): RedirectResponse
    {
        $user = $this->currentUser();

        $registration = ArtistRegistration::with('package')
            ->with('payment')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->where('status', ArtistRegistration::STATUS_PENDING_PAYMENT)
            ->firstOrFail();

        $transactionCode = (string) ($registration->payment?->transaction_code ?? '');
        if ($transactionCode === '') {
            return redirect()->route('artist-register.index')
                ->with('error', 'Đơn chờ thanh toán chưa có mã giao dịch hợp lệ. Vui lòng tạo lại đơn.');
        }

        $returnUrl = route('artist-register.vnpay.return');
        $orderInfo = $this->buildPaymentTitle('Dang ky goi Nghe Si', ($registration->package?->name ?? 'Goi Nghe Si') . ' tai Blue Wave Music');
        $vnpayUrl  = $this->buildVnpayUrl($returnUrl, $transactionCode, $orderInfo, (int) ($registration->package?->price ?? 0));

        return redirect()->away($vnpayUrl);
    }

    /**
     * Hủy một đơn pending_payment để user tạo lại từ đầu.
     */
    public function cancelPending(int $id): RedirectResponse
    {
        $user = $this->currentUser();

        $registration = ArtistRegistration::where('id', $id)
            ->where('user_id', $user->id)
            ->where('status', ArtistRegistration::STATUS_PENDING_PAYMENT)
            ->firstOrFail();

        DB::beginTransaction();
        try {
            $registration->delete();
            DB::commit();

            return redirect()->route('artist-register.index')
                ->with('success', 'Đã hủy đơn chờ thanh toán. Bạn có thể đăng ký lại ngay.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[VNPAY - Artist] Cancel pending error: ' . $e->getMessage());
            return redirect()->route('artist-register.index')
                ->with('error', 'Không thể hủy đơn chờ thanh toán.');
        }
    }

    /**
     * Xử lý kết quả VNPAY trả về.
     * GET /artist-register/vnpay/return
     */
    public function vnpayReturn(Request $request): RedirectResponse
    {
        $inputData = $this->vnpay->extractVnpInput($request);
        $vnpSecureHash = (string) ($inputData['vnp_SecureHash'] ?? '');

        $verification = $this->vnpay->verifySignature($inputData, $vnpSecureHash);
        \Illuminate\Support\Facades\Log::info('[VNPAY - Artist] Hash verification', [
            'expected' => $verification['expected'],
            'received' => $vnpSecureHash,
        ]);

        if (! $verification['valid']) {
            Log::warning('[VNPAY - Artist] Chữ ký không hợp lệ!', [
                'txnRef' => $inputData['vnp_TxnRef'] ?? null,
                'hashRaw' => $verification['hash_data'],
            ]);
            return redirect()->route('artist-register.index')
                ->with('error', 'Phản hồi thanh toán không hợp lệ.');
        }

        $payload = $verification['payload'];
        $txnRef         = $payload['vnp_TxnRef'] ?? '';
        $responseCode   = $payload['vnp_ResponseCode'] ?? '';
        $transactionStatus = $payload['vnp_TransactionStatus'] ?? '';

        $payment = Payment::with('payable')
            ->where('transaction_code', $txnRef)
            ->first();

        $registration = $payment?->payable;

        if (! $payment || ! ($registration instanceof ArtistRegistration)) {
            Log::error('Artist reg VNPAY return: registration not found', ['txnRef' => $txnRef]);
            return redirect()->route('artist-register.index')
                ->with('error', 'Không tìm thấy thông tin giao dịch.');
        }

        // Tránh xử lý lại
        if ($registration->status !== ArtistRegistration::STATUS_PENDING_PAYMENT) {
            return redirect()->route('artist-register.index')
                ->with('info', 'Giao dịch này đã được xử lý.');
        }

        DB::beginTransaction();
        try {
            if ($responseCode === '00' && $transactionStatus === '00') {
                // ── Thanh toán thành công ──────────────────────────────────────
                \Illuminate\Support\Facades\Log::info('[VNPAY - Artist] Thanh toán đăng ký nghệ sĩ thành công', ['txnRef' => $txnRef]);

                $payment->update([
                    'status'                    => 'paid',
                    'provider'                  => 'VNPAY',
                    'amount'                    => $registration->package?->price ?? $payment->amount,
                    'paid_at'                   => now(),
                    'provider_transaction_no'   => $inputData['vnp_TransactionNo'] ?? null,
                    'provider_pay_date'         => $inputData['vnp_PayDate'] ?? null,
                    'raw_response'              => $inputData,
                ]);

                $this->stateService->moveToPendingReviewAfterPayment($registration);

                DB::commit();

                // Tải lại để đảm bảo relations đủ dữ liệu
                $registration->load('user', 'package');

                // Gửi email xác nhận thanh toán cho user
                try {
                    Mail::to($registration->user->email)
                        ->send(new ArtistRegistrationPayment($registration));
                } catch (\Throwable $mailErr) {
                    Log::warning('Failed to send artist registration payment email: ' . $mailErr->getMessage());
                }

                // Gửi thông báo đến tất cả admin
                try {
                    $admins = User::whereHas('roles', fn ($query) => $query->where('slug', 'admin'))
                        ->where('deleted', false)
                        ->get();
                    foreach ($admins as $admin) {
                        $admin->notify(new NewArtistRegistration($registration));
                    }
                } catch (\Throwable $notifyErr) {
                    Log::warning('Failed to notify admins of artist registration: ' . $notifyErr->getMessage());
                }

                    return redirect()->route('artist.profile.setup')
                        ->with('success', '🎤 Thanh toán thành công! Vui lòng hoàn thiện hồ sơ nghệ sĩ để admin xem xét phê duyệt.');

            } else {
                // ── Thanh toán thất bại ────────────────────────────────────────
                \Illuminate\Support\Facades\Log::warning('[VNPAY - Artist] Giao dịch thất bại / Bị hủy', ['txnRef' => $txnRef, 'ResponseCode' => $responseCode]);

                $payment->update([
                    'status'                    => 'failed',
                    'provider'                  => 'VNPAY',
                    'amount'                    => $registration->package?->price ?? $payment->amount,
                    'paid_at'                   => null,
                    'provider_transaction_no'   => $inputData['vnp_TransactionNo'] ?? null,
                    'provider_pay_date'         => $inputData['vnp_PayDate'] ?? null,
                    'raw_response'              => $inputData,
                ]);

                $registration->delete();
                DB::commit();

                return redirect()->route('artist-register.index')
                    ->with('error', 'Thanh toán không thành công. Vui lòng thử lại. (Mã lỗi: ' . $responseCode . ')');
            }

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Artist reg VNPAY return processing error: ' . $e->getMessage());
            return redirect()->route('artist-register.index')
                ->with('error', 'Có lỗi xảy ra khi xử lý kết quả thanh toán.');
        }
    }
}
