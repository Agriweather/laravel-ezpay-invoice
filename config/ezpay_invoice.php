<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ezPay 電子發票 API 基本設定
    |--------------------------------------------------------------------------
    |
    | 此處設定您的 ezPay 商店基本資訊，包含商店代號、加密金鑰等。
    | 這些資訊可在 ezPay 商店後台的「系統開發管理」中取得。
    |
    */

    'merchant_id' => env('EZPAY_INVOICE_MERCHANT_ID', ''),

    'merchant_hash_key' => env('EZPAY_INVOICE_MERCHANT_HASH_KEY', ''),

    'merchant_hash_iv' => env('EZPAY_INVOICE_MERCHANT_HASH_IV', ''),

    'company_id' => env('EZPAY_INVOICE_COMPANY_ID', ''),

    'company_hash_key' => env('EZPAY_INVOICE_COMPANY_HASH_KEY', ''),

    'company_hash_iv' => env('EZPAY_INVOICE_COMPANY_HASH_IV', ''),

    /*
    |--------------------------------------------------------------------------
    | API 環境設定
    |--------------------------------------------------------------------------
    |
    | 設定 API 運行環境，test 為測試環境，production 為正式環境。
    | 測試階段請使用 test，正式上線時切換為 production。
    |
    */

    'env' => env('EZPAY_INVOICE_ENV', 'test'),

];
