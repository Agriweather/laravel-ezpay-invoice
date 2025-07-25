<?php

namespace Tests;

use Agriweather\EzpayInvoice\Enums\CarrierType;
use Agriweather\EzpayInvoice\Enums\CurrencyType;
use Agriweather\EzpayInvoice\Enums\InvoiceCategory;
use Agriweather\EzpayInvoice\Enums\InvoiceTerm;
use Agriweather\EzpayInvoice\Enums\InvoiceType;
use Agriweather\EzpayInvoice\Enums\TaxType;
use Illuminate\Support\Facades\Http;

class TestData
{
    /**
     * 基本的發票開立測試資料
     */
    public static function getBasicInvoiceData(): array
    {
        return [
            'MerchantOrderNo' => 'TEST-001',
            'Status' => 1, // 即時開立
            'Category' => InvoiceCategory::B2C->value,
            'BuyerName' => '測試客戶',
            'BuyerEmail' => 'test@example.com',
            'PrintFlag' => 'N',
            'TaxType' => TaxType::TAXABLE->value,
            'TaxRate' => 5,
            'Amt' => 100,
            'TaxAmt' => 5,
            'TotalAmt' => 105,
            'ItemName' => '測試商品',
            'ItemCount' => 1,
            'ItemUnit' => 'EA',
            'ItemPrice' => 100,
            'ItemAmt' => 100,
        ];
    }

    /**
     * B2B 發票開立測試資料
     */
    public static function getB2BInvoiceData(): array
    {
        return array_merge(self::getBasicInvoiceData(), [
            'Category' => InvoiceCategory::B2B->value,
            'BuyerName' => '測試公司',
            'BuyerUBN' => '12345678',
            'BuyerAddress' => '台北市信義區',
            'PrintFlag' => 'Y',
        ]);
    }

    /**
     * 載具發票測試資料
     */
    public static function getCarrierInvoiceData(): array
    {
        return array_merge(self::getBasicInvoiceData(), [
            'CarrierType' => CarrierType::MOBILE->value,
            'CarrierNum' => '/ABC.123',
        ]);
    }

    /**
     * 多品項發票測試資料
     */
    public static function getMultiItemInvoiceData(): array
    {
        return array_merge(self::getBasicInvoiceData(), [
            'Amt' => 300,
            'TaxAmt' => 15,
            'TotalAmt' => 315,
            'ItemName' => '商品A|商品B|商品C',
            'ItemCount' => '1|2|1',
            'ItemUnit' => 'EA|EA|EA',
            'ItemPrice' => '100|75|100',
            'ItemAmt' => '100|150|100',
        ]);
    }

    /**
     * 境外電商發票測試資料
     */
    public static function getCrossBorderInvoiceData(): array
    {
        return [
            'MerchantOrderNo' => 'CBT-001',
            'Status' => 1,
            'BuyerName' => 'John Doe',
            'BuyerAddress' => '123 Main St, New York, USA',
            'BuyerEmail' => 'john@example.com',
            'Category' => InvoiceCategory::B2C->value,
            'TaxType' => TaxType::TAXABLE->value,
            'TaxRate' => 5,
            'Currency' => CurrencyType::USD->value,
            'OriginalCurrencyAmount' => '105.50',
            'ExchangeRate' => '30.12300',
            'Amt' => '3166.15',
            'TaxAmt' => '158.31',
            'TotalAmt' => '3324.46',
            'ItemName' => 'Product A',
            'ItemCount' => '1.5',
            'ItemUnit' => 'EA',
            'ItemPrice' => '70.33',
            'ItemAmt' => '105.50',
            'Comment' => 'International purchase',
        ];
    }

    /**
     * 發票查詢測試資料
     */
    public static function getInvoiceSearchData(): array
    {
        return [
            'SearchType' => 0, // 發票號碼查詢
            'InvoiceNumber' => 'AA12345678',
            'RandomNum' => '1234',
        ];
    }

    /**
     * 訂單查詢測試資料
     */
    public static function getOrderSearchData(): array
    {
        return [
            'SearchType' => 1, // 訂單編號查詢
            'MerchantOrderNo' => 'TEST-001',
            'TotalAmt' => '105',
        ];
    }

    /**
     * 字軌管理測試資料
     */
    public static function getAlphanumericCodeData(): array
    {
        return [
            'Year' => 113,
            'Term' => InvoiceTerm::FIRST->value,
            'AlphabeticLetter' => 'AA',
            'StartNumber' => '00000001',
            'EndNumber' => '00009999',
            'InvoiceType' => InvoiceType::GENERAL->value,
        ];
    }

    /**
     * 手機條碼驗證測試資料
     */
    public static function getBarcodeVerificationData(): array
    {
        return [
            'BarCode' => '/ABC.122',
            'TimeStamp' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * 捐贈碼驗證測試資料
     */
    public static function getLoveCodeVerificationData(): array
    {
        return [
            'LoveCode' => '123',
            'TimeStamp' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * 成功的 API 回應模擬
     */
    public static function mockSuccessfulResponse(array $result = []): void
    {
        Http::fake([
            '*' => Http::response([
                'Status' => 'SUCCESS',
                'Message' => '',
                'Result' => array_merge([
                    'InvoiceTransNo' => '14061313541640927',
                    'MerchantID' => 'TEST_MERCHANT_ID',
                    'MerchantOrderNo' => 'TEST-001',
                    'RandomNum' => '1234',
                    'TotalAmt' => '105',
                    'CheckCode' => 'MOCK_CHECK_CODE',
                ], $result),
            ], 200),
        ]);
    }

    /**
     * 失敗的 API 回應模擬
     */
    public static function mockFailedResponse(string $status = 'LIB10003', string $message = '編號重複'): void
    {
        Http::fake([
            '*' => Http::response([
                'Status' => $status,
                'Message' => $message,
                'Result' => [],
            ], 200),
        ]);
    }

    /**
     * HTTP 連線錯誤模擬
     */
    public static function mockHttpError(): void
    {
        Http::fake([
            '*' => Http::response([], 500),
        ]);
    }

    /**
     * CheckCode 驗證失敗模擬
     */
    public static function mockInvalidCheckCode(): void
    {
        Http::fake([
            '*' => Http::response([
                'Status' => 'SUCCESS',
                'Message' => '',
                'Result' => [
                    'InvoiceTransNo' => '14061313541640927',
                    'MerchantID' => 'TEST_MERCHANT_ID',
                    'MerchantOrderNo' => 'TEST-001',
                    'RandomNum' => '1234',
                    'TotalAmt' => '105',
                    'CheckCode' => 'INVALID_CHECK_CODE',
                ],
            ], 200),
        ]);
    }

    /**
     * 取得測試用的 PostData 加密前資料
     */
    public static function getTestPostData(): string
    {
        return http_build_query(self::getBasicInvoiceData());
    }

    /**
     * 取得預期的 CheckCode 計算用資料
     */
    public static function getCheckCodeData(): array
    {
        return [
            'InvoiceTransNo' => '14061313541640927',
            'MerchantID' => 'TEST_MERCHANT_ID',
            'MerchantOrderNo' => 'TEST-001',
            'RandomNum' => '1234',
            'TotalAmt' => '105',
        ];
    }

    /**
     * 取得測試用的金鑰設定
     */
    public static function getTestCredentials(): array
    {
        return [
            'merchant_id' => 'TEST_MERCHANT_ID',
            'hash_key' => 'TEST_MERCHANT_HASH_KEY',
            'hash_iv' => 'TEST_MERCHANT_HASH_IV',
        ];
    }

    /**
     * 取得公司相關測試金鑰設定
     */
    public static function getCompanyTestCredentials(): array
    {
        return [
            'company_id' => 'TEST_COMPANY_ID',
            'hash_key' => 'TEST_COMPANY_HASH_KEY',
            'hash_iv' => 'TEST_COMPANY_HASH_IV',
        ];
    }
}
