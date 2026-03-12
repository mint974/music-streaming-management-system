<?php

return [
    /*
    |--------------------------------------------------------------------------
    | VNPAY Configuration
    |--------------------------------------------------------------------------
    | Sandbox: https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
    | Production: https://pay.vnpay.vn/vpcpay.html
    */

    'url'         => env('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
    'refund_url'  => env('VNPAY_REFUND_URL', 'https://sandbox.vnpayment.vn/merchant_webapi/api/transaction'),

    /*
    | sandbox = true  → VNPAY không thực sự xử lý hoàn tiền, bypass API call
    | sandbox = false → Gọi VNPAY Refund API thật (production)
    */
    'sandbox'     => env('VNPAY_SANDBOX', true),
    'tmn_code'    => env('VNPAY_TMN_CODE', '6UVFSI7P'),
    'hash_secret' => env('VNPAY_HASH_SECRET', 'QYJ0L47FZCT74DLZZBN8ZI3OK3PSQUYE'),
    'version'     => '2.1.0',
    'locale'      => 'vn',
    'currency'    => 'VND',

    /*
    | Tài khoản test (sandbox):
    | Ngân hàng : NCB
    | Số thẻ   : 9704198526191432198
    | Tên chủ  : NGUYEN VAN A
    | Ngày p.h : 07/15
    | OTP      : 123456
    | Xem thêm : https://sandbox.vnpayment.vn/apis/vnpay-demo/
    */
];
