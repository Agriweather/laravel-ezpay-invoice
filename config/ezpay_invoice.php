<?php

use Agriweather\EzpayInvoice\Enums\RespondType;

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

    'hash_key' => env('EZPAY_INVOICE_HASH_KEY', ''),

    'hash_iv' => env('EZPAY_INVOICE_HASH_IV', ''),

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

    /*
    |--------------------------------------------------------------------------
    | API 回傳格式設定
    |--------------------------------------------------------------------------
    |
    | 設定 API 回傳的資料格式，建議使用 JSON 格式。
    |
    */

    'respond_type' => RespondType::JSON,

    /*
    |--------------------------------------------------------------------------
    | API 版本設定
    |--------------------------------------------------------------------------
    |
    | 各個 API 的版本設定，請依據 ezPay 官方文件設定。
    |
    */

    'version' => [
        'invoice_issue' => '1.5',
        'invoice_invalid' => '1.0',
        'allowance_issue' => '1.3',
        'invoice_search' => '1.3',
    ],

    /*
    |--------------------------------------------------------------------------
    | API 端點 URL 設定
    |--------------------------------------------------------------------------
    |
    | ezPay 測試與正式環境的 API 端點 URL。
    |
    */

    'api_urls' => [
        'test' => [
            'invoice_issue' => 'https://cinv.ezpay.com.tw/Api/invoice_issue',
            'invoice_invalid' => 'https://cinv.ezpay.com.tw/Api/invoice_invalid',
            'allowance_issue' => 'https://cinv.ezpay.com.tw/Api/allowance_issue',
            'invoice_search' => 'https://cinv.ezpay.com.tw/Api/invoice_search',
        ],
        'production' => [
            'invoice_issue' => 'https://inv.ezpay.com.tw/Api/invoice_issue',
            'invoice_invalid' => 'https://inv.ezpay.com.tw/Api/invoice_invalid',
            'allowance_issue' => 'https://inv.ezpay.com.tw/Api/allowance_issue',
            'invoice_search' => 'https://inv.ezpay.com.tw/Api/invoice_search',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP 請求設定
    |--------------------------------------------------------------------------
    |
    | HTTP 請求的相關設定，包含超時時間等。
    |
    */

    'timeout' => 30,

];
