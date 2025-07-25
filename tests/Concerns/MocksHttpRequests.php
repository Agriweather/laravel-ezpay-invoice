<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\Http;

trait MocksHttpRequests
{
    /**
     * 模擬成功的 API 回應
     */
    protected function mockSuccessfulApiResponse(array $result = []): void
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
                    'CheckCode' => $this->generateMockCheckCode(),
                ], $result),
            ], 200),
        ]);
    }

    /**
     * 模擬失敗的 API 回應
     */
    protected function mockFailedApiResponse(string $status = 'LIB10003', string $message = '編號重複'): void
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
     * 模擬 HTTP 連線錯誤
     */
    protected function mockHttpConnectionError(): void
    {
        Http::fake([
            '*' => Http::response([], 500),
        ]);
    }

    /**
     * 模擬 CheckCode 驗證失敗
     */
    protected function mockInvalidCheckCodeResponse(): void
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
     * 模擬發票開立成功回應
     */
    protected function mockInvoiceIssueSuccess(array $customResult = []): void
    {
        $result = array_merge([
            'InvoiceTransNo' => '14061313541640927',
            'MerchantID' => 'TEST_MERCHANT_ID',
            'MerchantOrderNo' => 'TEST-001',
            'InvoiceNumber' => 'AA12345678',
            'RandomNum' => '1234',
            'CreateTime' => '2024-01-01 12:00:00',
            'InvoiceUrl' => 'https://test.ezpay.com.tw/invoice',
            'TotalAmt' => 105,
            'CheckCode' => $this->generateMockCheckCode(),
        ], $customResult);

        Http::fake([
            '*' => Http::response([
                'Status' => 'SUCCESS',
                'Message' => '',
                'Result' => $result,
            ], 200),
        ]);
    }

    /**
     * 模擬發票查詢成功回應
     */
    protected function mockInvoiceSearchSuccess(array $customResult = []): void
    {
        $result = array_merge([
            'InvoiceTransNo' => '14061313541640927',
            'MerchantID' => 'TEST_MERCHANT_ID',
            'MerchantOrderNo' => 'TEST-001',
            'InvoiceNumber' => 'AA12345678',
            'RandomNum' => '1234',
            'BuyerName' => '測試客戶',
            'BuyerEmail' => 'test@example.com',
            'TotalAmt' => 105,
            'InvoiceUrl' => 'https://test.ezpay.com.tw/invoice/view',
            'CheckCode' => $this->generateMockCheckCode(),
        ], $customResult);

        Http::fake([
            '*' => Http::response([
                'Status' => 'SUCCESS',
                'Message' => '',
                'Result' => $result,
            ], 200),
        ]);
    }

    /**
     * 模擬發票作廢成功回應
     */
    protected function mockInvoiceVoidSuccess(array $customResult = []): void
    {
        $result = array_merge([
            'InvoiceNumber' => 'AA12345678',
            'MerchantID' => 'TEST_MERCHANT_ID',
            'VoidTime' => '2024-01-01 15:00:00',
            'CheckCode' => $this->generateMockCheckCode(),
        ], $customResult);

        Http::fake([
            '*' => Http::response([
                'Status' => 'SUCCESS',
                'Message' => '',
                'Result' => $result,
            ], 200),
        ]);
    }

    /**
     * 模擬折讓開立成功回應
     */
    protected function mockAllowanceIssueSuccess(array $customResult = []): void
    {
        $result = array_merge([
            'InvoiceNumber' => 'AA12345678',
            'MerchantID' => 'TEST_MERCHANT_ID',
            'MerchantOrderNo' => 'TEST-001',
            'AllowanceNo' => 'ALLOW-001',
            'TotalAmt' => 50,
            'CheckCode' => $this->generateMockCheckCode(),
        ], $customResult);

        Http::fake([
            '*' => Http::response([
                'Status' => 'SUCCESS',
                'Message' => '',
                'Result' => $result,
            ], 200),
        ]);
    }

    /**
     * 模擬境外電商發票開立成功回應
     */
    protected function mockCrossBorderInvoiceSuccess(array $customResult = []): void
    {
        $result = array_merge([
            'InvoiceTransNo' => '14061313541640927',
            'MerchantID' => 'TEST_MERCHANT_ID',
            'MerchantOrderNo' => 'CBT-001',
            'InvoiceNumber' => 'AA12345678',
            'RandomNum' => '1234',
            'CreateTime' => '2024-01-01 12:00:00',
            'InvoiceUrl' => 'https://test.ezpay.com.tw/invoice',
            'Currency' => 'USD',
            'OriginalCurrencyAmount' => '105.50',
            'ExchangeRate' => '30.12300',
            'TotalAmt' => '3324.46',
            'CheckCode' => $this->generateMockCheckCode(),
        ], $customResult);

        Http::fake([
            '*' => Http::response([
                'Status' => 'SUCCESS',
                'Message' => '',
                'Result' => $result,
            ], 200),
        ]);
    }

    /**
     * 模擬字軌管理成功回應
     */
    protected function mockAlphanumericCodeSuccess(array $customResult = []): void
    {
        $result = array_merge([
            'CompanyId' => 'TEST_COMPANY_ID',
            'ManagementNo' => '00455ujp8',
            'Year' => 113,
            'Term' => 1,
            'AlphabeticLetter' => 'AA',
            'StartNumber' => '00000001',
            'EndNumber' => '00009999',
            'CheckCode' => $this->generateMockAlphanumericCheckCode(),
        ], $customResult);

        Http::fake([
            '*' => Http::response([
                'Status' => 'SUCCESS',
                'Message' => '',
                'Result' => $result,
            ], 200),
        ]);
    }

    /**
     * 模擬手機條碼驗證成功回應
     */
    protected function mockBarcodeVerificationSuccess(bool $exists = true): void
    {
        Http::fake([
            '*' => Http::response([
                'Status' => 'SUCCESS',
                'Message' => '',
                'Result' => [
                    'BarCode' => '/ABC.122',
                    'IsExist' => $exists ? 'Y' : 'N',
                ],
            ], 200),
        ]);
    }

    /**
     * 模擬捐贈碼驗證成功回應
     */
    protected function mockLoveCodeVerificationSuccess(bool $exists = true): void
    {
        Http::fake([
            '*' => Http::response([
                'Status' => 'SUCCESS',
                'Message' => '',
                'Result' => [
                    'LoveCode' => '123',
                    'IsExist' => $exists ? 'Y' : 'N',
                ],
            ], 200),
        ]);
    }

    /**
     * 產生模擬的 CheckCode（用於發票與折讓 API）
     */
    protected function generateMockCheckCode(): string
    {
        $data = [
            'InvoiceTransNo' => '14061313541640927',
            'MerchantID' => 'TEST_MERCHANT_ID',
            'MerchantOrderNo' => 'TEST-001',
            'RandomNum' => '1234',
            'TotalAmt' => '105',
        ];

        ksort($data);
        $checkStr = http_build_query($data);

        return strtoupper(hash('sha256', 'HashIV=TEST_MERCHANT_HASH_IV&'.$checkStr.'&HashKey=TEST_MERCHANT_HASH_KEY'));
    }

    /**
     * 產生模擬的字軌管理 CheckCode
     */
    protected function generateMockAlphanumericCheckCode(): string
    {
        $data = [
            'AlphabeticLetter' => 'AA',
            'CompanyId' => 'TEST_COMPANY_ID',
            'EndNumber' => '00009999',
            'ManagementNo' => '00455ujp8',
            'StartNumber' => '00000001',
        ];

        ksort($data);
        $checkStr = http_build_query($data);

        return strtoupper(hash('sha256', 'HashIV=TEST_COMPANY_HASH_IV&'.$checkStr.'&HashKey=TEST_COMPANY_HASH_KEY'));
    }

    /**
     * 斷言 HTTP 請求已發送
     */
    protected function assertHttpRequestSent(string $url, array $expectedData = []): void
    {
        Http::assertSent(function ($request) use ($url, $expectedData) {
            if (! str_contains($request->url(), $url)) {
                return false;
            }

            if (! empty($expectedData)) {
                $requestData = $request->data();
                foreach ($expectedData as $key => $value) {
                    if (! isset($requestData[$key]) || $requestData[$key] !== $value) {
                        return false;
                    }
                }
            }

            return true;
        });
    }

    /**
     * 斷言 HTTP 請求包含加密的 PostData_
     */
    protected function assertHttpRequestContainsEncryptedData(): void
    {
        Http::assertSent(function ($request) {
            $data = $request->data();

            // 檢查是否包含必要欄位
            return isset($data['MerchantID_']) &&
                   isset($data['PostData_']) &&
                   ! empty($data['PostData_']);
        });
    }
}
