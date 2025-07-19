<?php

namespace Agriweather\EzpayInvoice;

use Agriweather\EzpayInvoice\Crypto\EzpayCrypto;
use Agriweather\EzpayInvoice\Exceptions\EzpayInvoiceException;
use Illuminate\Http\Client\Factory as HttpClient;

/**
 * ezPay 電子發票 API 主要服務類別
 */
class EzpayInvoice
{
    private EzpayCrypto $crypto;

    private HttpClient $httpClient;

    private array $config;

    /**
     * 建立服務實例
     *
     * @param  EzpayCrypto  $crypto  加密服務
     * @param  HttpClient  $httpClient  HTTP 客戶端
     */
    public function __construct(EzpayCrypto $crypto, HttpClient $httpClient)
    {
        $this->crypto = $crypto;
        $this->httpClient = $httpClient;
        $this->config = config('ezpay_invoice');
    }

    /**
     * 開立發票
     *
     * @param  array  $invoiceData  發票資料
     * @return array API 回應結果
     *
     * @throws EzpayInvoiceException
     */
    public function issue(array $invoiceData): array
    {
        $apiEndpoint = 'invoice_issue';

        return $this->sendRequest($apiEndpoint, $invoiceData);
    }

    /**
     * 作廢發票
     *
     * @param  string  $invoiceNumber  發票號碼
     * @param  string  $reason  作廢原因
     * @return array API 回應結果
     *
     * @throws EzpayInvoiceException
     */
    public function invalid(string $invoiceNumber, string $reason): array
    {
        $data = [
            'InvoiceNumber' => $invoiceNumber,
            'InvalidReason' => $reason,
        ];

        $apiEndpoint = 'invoice_invalid';

        return $this->sendRequest($apiEndpoint, $data);
    }

    /**
     * 開立折讓
     *
     * @param  array  $allowanceData  折讓資料
     * @return array API 回應結果
     *
     * @throws EzpayInvoiceException
     */
    public function issueAllowance(array $allowanceData): array
    {
        $apiEndpoint = 'allowance_issue';

        return $this->sendRequest($apiEndpoint, $allowanceData);
    }

    /**
     * 查詢發票
     *
     * @param  array  $searchData  查詢條件
     * @return array API 回應結果
     *
     * @throws EzpayInvoiceException
     */
    public function search(array $searchData): array
    {
        $apiEndpoint = 'invoice_search';

        return $this->sendRequest($apiEndpoint, $searchData);
    }

    /**
     * 發送 API 請求
     *
     * @param  string  $apiEndpoint  API 端點名稱
     * @param  array  $data  請求資料
     * @return array 解析後的回應資料
     *
     * @throws EzpayInvoiceException
     */
    private function sendRequest(string $apiEndpoint, array $data): array
    {
        // 準備請求資料
        $data['MerchantID'] = $this->config['merchant_id'];
        $data['Version'] = $this->config['version'][$apiEndpoint];
        $data['RespondType'] = $this->config['respond_type']->value;
        $data['TimeStamp'] = time();

        // 將資料轉換為查詢字串並加密
        $queryString = http_build_query($data);
        $encryptedData = $this->crypto->encrypt($queryString);

        // 準備 POST 資料
        $postData = [
            'MerchantID_' => $this->config['merchant_id'],
            'PostData_' => $encryptedData,
        ];

        // 取得 API URL
        $url = $this->getApiUrl($apiEndpoint);

        // 發送請求
        $response = $this->httpClient->timeout($this->config['timeout'])
            ->asForm()
            ->post($url, $postData);

        if (! $response->successful()) {
            throw new EzpayInvoiceException(
                'HTTP 請求失敗',
                'HTTP_ERROR',
                $response->status()
            );
        }

        // 解析回應
        $responseData = $response->json();

        if (! $responseData || ! isset($responseData['Status'])) {
            throw new EzpayInvoiceException(
                '無效的 API 回應格式',
                'INVALID_RESPONSE'
            );
        }

        // 檢查 API 狀態
        if ($responseData['Status'] !== 'SUCCESS') {
            throw new EzpayInvoiceException(
                $responseData['Message'] ?? '未知錯誤',
                $responseData['Status'] ?? 'UNKNOWN_ERROR'
            );
        }

        // 驗證 CheckCode（如果存在）
        if (isset($responseData['Result']['CheckCode'])) {
            $resultData = $responseData['Result'];
            $checkCode = $resultData['CheckCode'];
            unset($resultData['CheckCode']);

            if (! $this->crypto->verifyCheckCode($resultData, $checkCode)) {
                throw new EzpayInvoiceException(
                    'CheckCode 驗證失敗',
                    'CHECKCODE_ERROR'
                );
            }
        }

        return $responseData;
    }

    /**
     * 取得 API URL
     *
     * @param  string  $apiEndpoint  API 端點名稱
     * @return string 完整的 API URL
     */
    private function getApiUrl(string $apiEndpoint): string
    {
        $env = $this->config['env'];

        return $this->config['api_urls'][$env][$apiEndpoint];
    }
}
