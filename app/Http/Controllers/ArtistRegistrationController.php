<?php

namespace App\Http\Controllers;

use App\Mail\ArtistRegistrationPayment;
use App\Models\ArtistPackage;
use App\Models\ArtistRegistration;
use App\Models\User;
use App\Notifications\NewArtistRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ArtistRegistrationController extends Controller
{
    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function currentUser(): User
    {
        /** @var User $user */
        $user = Auth::user();
        return $user;
    }

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

    private function buildVnpayUrl(string $returnUrl, string $txnRef, string $orderInfo, int $amount): string
    {
        date_default_timezone_set('Asia/Ho_Chi_Minh');

        $inputData = [
            'vnp_Version'    => config('vnpay.version', '2.1.0'),
            'vnp_TmnCode'    => config('vnpay.tmn_code'),
            'vnp_Amount'     => $amount * 100,
            'vnp_Command'    => 'pay',
            'vnp_CreateDate' => date('YmdHis'),
            'vnp_CurrCode'   => config('vnpay.currency', 'VND'),
            'vnp_IpAddr'     => $this->getIpAddress(),
            'vnp_Locale'     => config('vnpay.locale', 'vn'),
            'vnp_OrderInfo'  => $orderInfo,
            'vnp_OrderType'  => 'billpayment',
            'vnp_ReturnUrl'  => $returnUrl,
            'vnp_TxnRef'     => $txnRef,
        ];

        ksort($inputData);

        $queryStr = '';
        $hashData = '';
        foreach ($inputData as $key => $value) {
            $queryStr .= urlencode($key) . '=' . urlencode($value) . '&';
            $hashData .= urlencode($key) . '=' . urlencode($value) . '&';
        }
        $hashData = rtrim($hashData, '&');

        $secureHash = hash_hmac('sha512', $hashData, config('vnpay.hash_secret'));

        return config('vnpay.url') . '?' . $queryStr . 'vnp_SecureHash=' . $secureHash;
    }

    // ─── Actions ──────────────────────────────────────────────────────────────

    /**
     * Trang giới thiệu gói đăng ký nghệ sĩ.
     * GET /artist-register
     */
    public function index(): View|RedirectResponse
    {
        $user = $this->currentUser();

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
            ->where('user_id', $user->id)
            ->whereNotIn('status', ['pending_payment'])
            ->latest()
            ->get();

        return view('pages.artist-register', compact('packages', 'pending', 'expiredRegistration', 'registrationHistory'));
    }

    /**
     * Hiển thị form đăng ký chi tiết cho gói đã chọn.
     * GET /artist-register/{packageId}
     */
    public function create(int $packageId): View|RedirectResponse
    {
        $user    = $this->currentUser();
        $package = ArtistPackage::active()->findOrFail($packageId);

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

        // Bị thu hồi vĩnh viễn
        if ($user->isArtistRevoked()) {
            return redirect()->route('dashboard')
                ->with('error', 'Quyền Nghệ sĩ của bạn đã bị thu hồi vĩnh viễn.');
        }

        if ($user->isArtist() && !$user->isArtistPackageExpired()) {
            return redirect()->route('artist.dashboard');
        }

        if ($user->hasPendingArtistRegistration()) {
            return redirect()->route('artist-register.index')
                ->with('warning', 'Bạn đã có đơn đăng ký đang được xử lý.');
        }

        $request->validate([
            'artist_name' => ['required', 'string', 'max:100', 'min:2'],
            'bio'         => ['nullable', 'string', 'max:1000'],
        ], [
            'artist_name.required' => 'Vui lòng nhập tên nghệ danh.',
            'artist_name.min'      => 'Tên nghệ danh phải có ít nhất 2 ký tự.',
            'artist_name.max'      => 'Tên nghệ danh không được vượt quá 100 ký tự.',
        ]);

        DB::beginTransaction();
        try {
            // Hủy các đơn pending_payment cũ (mắc kẹt)
            ArtistRegistration::where('user_id', $user->id)
                ->where('status', 'pending_payment')
                ->delete();

            $txnRef = 'ART_' . $user->id . '_' . time();

            $registration = ArtistRegistration::create([
                'user_id'          => $user->id,
                'package_id'       => $package->id,
                'artist_name'      => $request->input('artist_name'),
                'bio'              => $request->input('bio'),
                'status'           => 'pending_payment',
                'amount_paid'      => $package->price,
                'transaction_code' => $txnRef,
            ]);

            DB::commit();

            $returnUrl = route('artist-register.vnpay.return');
            $orderInfo = 'Dang ky goi Nghe Si tai Blue Wave Music';
            $vnpayUrl  = $this->buildVnpayUrl($returnUrl, $txnRef, $orderInfo, $package->price);

            return redirect()->away($vnpayUrl);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Artist registration checkout error: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi khởi tạo thanh toán. Vui lòng thử lại.');
        }
    }

    /**
     * Xử lý kết quả VNPAY trả về.
     * GET /artist-register/vnpay/return
     */
    public function vnpayReturn(Request $request): RedirectResponse
    {
        $inputData     = $request->all();
        $vnpSecureHash = $inputData['vnp_SecureHash'] ?? '';

        unset($inputData['vnp_SecureHash'], $inputData['vnp_SecureHashType']);

        ksort($inputData);
        $hashRaw = '';
        foreach ($inputData as $key => $value) {
            $hashRaw .= urlencode($key) . '=' . urlencode($value) . '&';
        }
        $hashRaw = rtrim($hashRaw, '&');

        $expectedHash = hash_hmac('sha512', $hashRaw, config('vnpay.hash_secret'));

        if (!hash_equals($expectedHash, $vnpSecureHash)) {
            Log::warning('Artist reg VNPAY invalid hash', ['txnRef' => $inputData['vnp_TxnRef'] ?? null]);
            return redirect()->route('artist-register.index')
                ->with('error', 'Phản hồi thanh toán không hợp lệ.');
        }

        $txnRef         = $inputData['vnp_TxnRef']          ?? '';
        $responseCode   = $inputData['vnp_ResponseCode']     ?? '';
        $transactionStatus = $inputData['vnp_TransactionStatus'] ?? '';

        $registration = ArtistRegistration::with(['user', 'package'])
            ->where('transaction_code', $txnRef)
            ->first();

        if (!$registration) {
            Log::error('Artist reg VNPAY return: registration not found', ['txnRef' => $txnRef]);
            return redirect()->route('artist-register.index')
                ->with('error', 'Không tìm thấy thông tin giao dịch.');
        }

        // Tránh xử lý lại
        if ($registration->status !== 'pending_payment') {
            return redirect()->route('artist-register.index')
                ->with('info', 'Giao dịch này đã được xử lý.');
        }

        DB::beginTransaction();
        try {
            if ($responseCode === '00' && $transactionStatus === '00') {
                // ── Thanh toán thành công ──────────────────────────────────────

                $registration->update([
                    'status'             => 'pending_review',
                    'paid_at'            => now(),
                    'vnp_transaction_no' => $inputData['vnp_TransactionNo'] ?? null,
                    'vnp_pay_date'       => $inputData['vnp_PayDate'] ?? null,
                ]);

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
                    $admins = User::where('role', 'admin')->where('deleted', false)->get();
                    foreach ($admins as $admin) {
                        $admin->notify(new NewArtistRegistration($registration));
                    }
                } catch (\Throwable $notifyErr) {
                    Log::warning('Failed to notify admins of artist registration: ' . $notifyErr->getMessage());
                }

                return redirect()->route('artist-register.index')
                    ->with('success', '🎤 Thanh toán thành công! Đơn đăng ký của bạn đang được xét duyệt. Email xác nhận đã được gửi về hộp thư của bạn.');

            } else {
                // ── Thanh toán thất bại ────────────────────────────────────────

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
