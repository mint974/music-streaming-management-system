<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VnpayPaymentService
{
    public function buildPaymentTitle(string $prefix, string $title): string
    {
        $normalized = Str::ascii($prefix . ' ' . $title);
        $normalized = preg_replace('/[^A-Za-z0-9 ]+/', ' ', $normalized) ?? $normalized;
        $normalized = preg_replace('/\s+/', ' ', trim($normalized)) ?? trim($normalized);

        return mb_substr($normalized, 0, 255);
    }

    public function getClientIp(): string
    {
        $request = request();
        if ($request instanceof Request) {
            return (string) $request->ip();
        }

        return '127.0.0.1';
    }

    public function buildQueryAndHashData(array $inputData): array
    {
        ksort($inputData);

        $query = '';
        $hashData = '';

        foreach ($inputData as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $encoded = urlencode((string) $key) . '=' . urlencode((string) $value);
            $query .= ($query === '' ? '' : '&') . $encoded;
            $hashData .= ($hashData === '' ? '' : '&') . $encoded;
        }

        return [$query, $hashData];
    }

    public function buildVnpayUrl(
        string $returnUrl,
        string $txnRef,
        string $orderInfo,
        int $amount,
        bool $withExpireDate = false
    ): string {
        date_default_timezone_set('Asia/Ho_Chi_Minh');

        $inputData = [
            'vnp_Version' => config('vnpay.version', '2.1.0'),
            'vnp_TmnCode' => config('vnpay.tmn_code'),
            'vnp_Amount' => $amount * 100,
            'vnp_Command' => 'pay',
            'vnp_CreateDate' => date('YmdHis'),
            'vnp_CurrCode' => config('vnpay.currency', 'VND'),
            'vnp_IpAddr' => $this->getClientIp(),
            'vnp_Locale' => config('vnpay.locale', 'vn'),
            'vnp_OrderInfo' => $orderInfo,
            'vnp_OrderType' => 'other',
            'vnp_ReturnUrl' => $returnUrl,
            'vnp_TxnRef' => $txnRef,
        ];

        if ($withExpireDate) {
            $start = date('YmdHis');
            $inputData['vnp_ExpireDate'] = date('YmdHis', strtotime('+15 minutes', strtotime($start)));
        }

        [$query, $hashData] = $this->buildQueryAndHashData($inputData);
        $secureHash = hash_hmac('sha512', $hashData, (string) config('vnpay.hash_secret'));

        return (string) config('vnpay.url') . '?' . $query . '&vnp_SecureHash=' . $secureHash;
    }

    public function extractVnpInput(Request $request): array
    {
        $input = [];
        foreach ($request->query() as $key => $value) {
            if (str_starts_with((string) $key, 'vnp_')) {
                $input[$key] = $value;
            }
        }

        return $input;
    }

    public function verifySignature(array $inputData, string $receivedHash): array
    {
        $payload = $inputData;
        unset($payload['vnp_SecureHash'], $payload['vnp_SecureHashType']);

        [, $hashData] = $this->buildQueryAndHashData($payload);
        $expectedHash = hash_hmac('sha512', $hashData, (string) config('vnpay.hash_secret'));

        return [
            'valid' => hash_equals($expectedHash, $receivedHash),
            'expected' => $expectedHash,
            'hash_data' => $hashData,
            'payload' => $payload,
        ];
    }
}
