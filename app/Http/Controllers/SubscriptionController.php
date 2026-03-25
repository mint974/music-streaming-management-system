<?php

namespace App\Http\Controllers;

use App\Mail\SubscriptionConfirmation;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Vip;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    // ─── Helpers ──────────────────────────────────────────────────────────────

    /** @return User */
    private function currentUser(): User
    {
        /** @var User $user */
        $user = Auth::user();
        return $user;
    }

    /**
     * Lấy IP của người dùng.
     */
    private function getIpAddress(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        }
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Tạo URL thanh toán VNPAY.
     */
    private function buildVnpayUrl(string $returnUrl, string $txnRef, string $orderInfo, int $amount): string
    {
        date_default_timezone_set('Asia/Ho_Chi_Minh');

        $inputData = [
            'vnp_Version'   => config('vnpay.version', '2.1.0'),
            'vnp_TmnCode'   => config('vnpay.tmn_code'),
            'vnp_Amount'    => $amount * 100,          // đơn vị nhỏ nhất (xu)
            'vnp_Command'   => 'pay',
            'vnp_CreateDate'=> date('YmdHis'),
            'vnp_CurrCode'  => config('vnpay.currency', 'VND'),
            'vnp_IpAddr'    => $this->getIpAddress(),
            'vnp_Locale'    => config('vnpay.locale', 'vn'),
            'vnp_OrderInfo' => $orderInfo,
            'vnp_OrderType' => 'billpayment',
            'vnp_ReturnUrl' => $returnUrl,
            'vnp_TxnRef'    => $txnRef,
        ];

        ksort($inputData);

        $queryStr  = '';
        $hashData  = '';
        foreach ($inputData as $key => $value) {
            $queryStr .= urlencode($key) . '=' . urlencode($value) . '&';
            $hashData .= urlencode($key) . '=' . urlencode($value) . '&';
        }
        $hashData = rtrim($hashData, '&');

        $secureHash = hash_hmac('sha512', $hashData, config('vnpay.hash_secret'));
        $url = config('vnpay.url') . '?' . $queryStr . 'vnp_SecureHash=' . $secureHash;

        return $url;
    }

    // ─── Actions ──────────────────────────────────────────────────────────────

    public function __construct() {}

    /**
     * Trang quản lý gói đăng ký của người dùng.
     * - Gói đang dùng  (nếu có)
     * - Danh sách gói VIP để nâng cấp
     * - Lịch sử đăng ký + trạng thái thanh toán
     */
    public function index(): View
    {
        $user = $this->currentUser();

        $activeSub  = $user->activeSubscription();
        $vips       = Vip::active()->orderBy('price')->get();
        $history    = Subscription::with(['vip', 'payment'])
                        ->where('user_id', $user->id)
                        ->latest()
                        ->paginate(8);

        return view('pages.subscription', compact('activeSub', 'vips', 'history'));
    }

    /**
     * Khởi tạo thanh toán VNPAY để mua / gia hạn gói VIP.
     *
     * POST /subscription/checkout/{vipId}
     */
    public function checkout(string $vipId): RedirectResponse
    {
        $vip = Vip::active()->findOrFail($vipId);
        $user = $this->currentUser();

        DB::beginTransaction();
        try {
            // Xóa các gói pending cũ (nếu mắc kẹt chưa trả) của user thay vì lưu lịch sử lộn xộn
            $pendingSubs = Subscription::where('user_id', $user->id)
                ->where('status', 'pending')
                ->get();
            foreach ($pendingSubs as $ps) {
                $ps->payment()->delete();
                $ps->delete();
            }

            // Tạo subscription mới ở trạng thái pending
            $startDate = Carbon::today();
            $endDate   = $startDate->copy()->addDays($vip->duration_days);

            $subscription = Subscription::create([
                'user_id'     => $user->id,
                'vip_id'      => $vip->id,
                'start_date'  => $startDate,
                'end_date'    => $endDate,
                'status'      => 'pending',
                'amount_paid' => $vip->price,
            ]);

            // Mã giao dịch độc nhất
            $txnRef = 'SUB_' . $subscription->id . '_' . time();

            // Tạo payment đang chờ
            Payment::create([
                'subscription_id' => $subscription->id,
                'method'          => 'VNPAY',
                'status'          => 'pending',
                'transaction_code'=> $txnRef,
                'date'            => null,
            ]);

            DB::commit();

            // Tạo URL VNPAY
            $returnUrl = route('subscription.vnpay.return');
            $orderInfo = 'Thanh toan goi ' . $vip->title . ' tai Blue Wave Music';
            $vnpayUrl  = $this->buildVnpayUrl($returnUrl, $txnRef, $orderInfo, $vip->price);

            return redirect()->away($vnpayUrl);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('VNPAY checkout error: ' . $e->getMessage());
            return redirect()->route('subscription.index')
                ->with('error', 'Có lỗi xảy ra khi khởi tạo thanh toán. Vui lòng thử lại.');
        }
    }

    /**
     * Xử lý kết quả trả về từ VNPAY.
     *
     * GET /subscription/vnpay/return
     */
    public function vnpayReturn(Request $request): RedirectResponse
    {
        $inputData     = $request->all();
        $vnpSecureHash = $inputData['vnp_SecureHash'] ?? '';

        // Bỏ các field hash ra trước khi ký lại
        unset($inputData['vnp_SecureHash'], $inputData['vnp_SecureHashType']);

        ksort($inputData);
        $hashRaw = '';
        foreach ($inputData as $key => $value) {
            $hashRaw .= urlencode($key) . '=' . urlencode($value) . '&';
        }
        $hashRaw = rtrim($hashRaw, '&');

        $expectedHash = hash_hmac('sha512', $hashRaw, config('vnpay.hash_secret'));

        // Nếu chữ ký không khớp → có thể bị giả mạo
        if (!hash_equals($expectedHash, $vnpSecureHash)) {
            Log::warning('VNPAY invalid hash', ['txnRef' => $inputData['vnp_TxnRef'] ?? null]);
            return redirect()->route('subscription.index')
                ->with('error', 'Phản hồi thanh toán không hợp lệ. Vui lòng liên hệ hỗ trợ.');
        }

        $txnRef          = $inputData['vnp_TxnRef']          ?? '';
        $responseCode    = $inputData['vnp_ResponseCode']     ?? '';
        $transactionStatus = $inputData['vnp_TransactionStatus'] ?? '';

        $payment = Payment::with('subscription.user', 'subscription.vip')
            ->where('transaction_code', $txnRef)
            ->first();

        if (!$payment) {
            Log::error('VNPAY return: payment not found', ['txnRef' => $txnRef]);
            return redirect()->route('subscription.index')
                ->with('error', 'Không tìm thấy thông tin giao dịch.');
        }

        $subscription = $payment->subscription;

        // Tránh xử lý lại nếu đã resolved
        if (!$payment->isPending()) {
            return redirect()->route('subscription.index')
                ->with('info', 'Giao dịch này đã được xử lý trước đó.');
        }

        DB::beginTransaction();
        try {
            if ($responseCode === '00' && $transactionStatus === '00') {
                // ── Thanh toán thành công ─────────────────────────────────────

                $user = $subscription->user;

                // Hủy gói active cũ (nếu có) trước khi kích hoạt gói mới
                Subscription::where('user_id', $user->id)
                    ->where('status', 'active')
                    ->update(['status' => 'cancelled']);

                // Kích hoạt subscription mới
                $subscription->update(['status' => 'active']);

                // Cập nhật payment
                $payment->update([
                    'status'             => 'paid',
                    'date'               => now(),
                    'vnp_transaction_no' => $inputData['vnp_TransactionNo'] ?? null,
                    'vnp_pay_date'       => $inputData['vnp_PayDate'] ?? null,
                ]);

                // Nâng cấp role user lên premium
                if ($user->role !== 'admin' && $user->role !== 'artist') {
                    $user->update(['role' => 'premium']);
                }

                DB::commit();

                // Tải lại relations để gửi email
                $subscription->load('user', 'vip', 'payment');

                try {
                    Mail::to($user->email)->send(new SubscriptionConfirmation($subscription));
                } catch (\Throwable $mailErr) {
                    Log::warning('Failed to send subscription confirmation email: ' . $mailErr->getMessage());
                }

                return redirect()->route('subscription.index')
                    ->with('success', '🎉 Nâng cấp Premium thành công! Email xác nhận đã được gửi về hộp thư của bạn.');

            } else {
                // ── Thanh toán thất bại / bị hủy ─────────────────────────────

                $payment->update(['status' => 'failed']);
                $subscription->update(['status' => 'cancelled']);

                DB::commit();

                $errorMsg = $this->vnpayErrorMessage($responseCode);
                return redirect()->route('subscription.index')
                    ->with('error', "Thanh toán không thành công. {$errorMsg} (Mã lỗi: {$responseCode})");
            }

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('VNPAY return processing error: ' . $e->getMessage());
            return redirect()->route('subscription.index')
                ->with('error', 'Có lỗi xảy ra khi xử lý kết quả thanh toán.');
        }
    }

    /**
     * Hủy gói đăng ký đang hoạt động.
     *
     * POST /subscription/{id}/cancel
     */
    public function cancel(int $id): RedirectResponse
    {
        $user = $this->currentUser();
        $subscription = Subscription::with('vip', 'payment')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->firstOrFail();

        DB::beginTransaction();
        try {
            $subscription->update(['status' => 'cancelled']);

            // Hạ xuống free nếu không còn gói active nào
            if (!$user->activeSubscription()) {
                if ($user->role === 'premium') {
                    $user->update(['role' => 'free']);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Subscription cancel error: ' . $e->getMessage());
            return redirect()->route('subscription.index')
                ->with('error', 'Có lỗi xảy ra khi hủy đăng ký.');
        }

        $fresh   = $user->fresh();
        $roleMsg = $fresh->isPremium() ? '' : ' Tài khoản của bạn đã trở về Free.';

        return redirect()->route('subscription.index')
            ->with('success', 'Đã hủy gói đăng ký.' . $roleMsg);
    }

    /**
     * Thanh toán tiếp tục gói đăng ký pending
     *
     * POST /subscription/{id}/pay-pending
     */
    public function payPending(int $id): RedirectResponse
    {
        $user = $this->currentUser();
        $subscription = Subscription::with('vip', 'payment')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $vip = $subscription->vip;
        $payment = $subscription->payment;

        // Tạo mã giao dịch VNPAY mới để tránh lỗi trùng lặp khi quét
        $txnRef = 'SUB_' . $subscription->id . '_' . time();
        if ($payment) {
            $payment->update(['transaction_code' => $txnRef]);
        }

        // Tạo URL VNPAY
        $returnUrl = route('subscription.vnpay.return');
        $orderInfo = 'Thanh toan goi ' . $vip->title . ' tai Blue Wave Music';
        $vnpayUrl  = $this->buildVnpayUrl($returnUrl, $txnRef, $orderInfo, $vip->price);

        return redirect()->away($vnpayUrl);
    }

    /**
     * Hủy bỏ hóa đơn chờ thanh toán.
     *
     * POST /subscription/{id}/cancel-pending
     */
    public function cancelPending(int $id): RedirectResponse
    {
        $user = $this->currentUser();
        $subscription = Subscription::with('payment')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->firstOrFail();

        DB::beginTransaction();
        try {
            if ($subscription->payment) {
                $subscription->payment()->delete();
            }
            $subscription->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Cancel pending error: ' . $e->getMessage());
            return redirect()->route('subscription.index')
                ->with('error', 'Có lỗi xảy ra khi hủy gói chờ thanh toán.');
        }

        return redirect()->route('subscription.index')
            ->with('success', 'Đã hủy gói chờ thanh toán thành công.');
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function vnpayErrorMessage(string $code): string
    {
        return match ($code) {
            '07'    => 'Trừ tiền thành công, giao dịch bị nghi ngờ (liên quan đến lừa đảo).',
            '09'    => 'Thẻ/Tài khoản chưa đăng ký dịch vụ InternetBanking.',
            '10'    => 'Xác thực thông tin thẻ/tài khoản quá 3 lần.',
            '11'    => 'Đã hết hạn chờ thanh toán.',
            '12'    => 'Thẻ/Tài khoản bị khóa.',
            '13'    => 'Sai mật khẩu OTP.',
            '24'    => 'Giao dịch bị hủy bởi người dùng.',
            '51'    => 'Tài khoản không đủ số dư.',
            '65'    => 'Vượt quá hạn mức giao dịch trong ngày.',
            '75'    => 'Ngân hàng thanh toán đang bảo trì.',
            '79'    => 'Nhập sai mật khẩu thanh toán quá số lần quy định.',
            default => 'Vui lòng kiểm tra lại hoặc thử phương thức khác.',
        };
    }
}
