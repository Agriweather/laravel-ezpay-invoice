<?php

namespace Agriweather\EzpayInvoice\Builders;

use Agriweather\EzpayInvoice\Contracts\FormPostSender;
use Agriweather\EzpayInvoice\Enums\DisplayFlag;
use Agriweather\EzpayInvoice\Enums\SearchType;
use Agriweather\EzpayInvoice\Options\InvoiceQueryOptions;
use Agriweather\EzpayInvoice\Results\InvoiceQueryResult;
use Agriweather\EzpayInvoice\Results\InvoiceQueryUrlResult;
use Illuminate\Http\Response;

class InvoiceQueryBuilder extends Builder
{
    protected InvoiceQueryOptions $options;

    protected FormPostSender $formPostSender;

    protected function boot(): void
    {
        $this->crypto->setHashKey($this->factory->config('merchant_hash_key'));
        $this->crypto->setHashIv($this->factory->config('merchant_hash_iv'));

        $this->options = new InvoiceQueryOptions;
        $this->options->merchantId = $this->factory->config('merchant_id');
    }

    public function getOptions(): InvoiceQueryOptions
    {
        return $this->options;
    }

    /**
     * 透過發票號碼查詢 (需帶入 發票號碼 + 隨機碼 查詢)
     */
    public function withInvoice(string $invoiceNumber): self
    {
        $this->options->invoiceNumber = $invoiceNumber;
        $this->options->searchType = SearchType::BY_INVOICE_NUMBER;

        return $this;
    }

    /**
     * 透過隨機碼查詢 (需帶入 發票號碼 + 隨機碼 查詢)
     */
    public function withRandomNumber(string $randomNumber): self
    {
        $this->options->randomNumber = $randomNumber;

        return $this;
    }

    /**
     * 透過訂單編號查詢 (需帶入 訂單編號 + 發票金額 查詢)
     */
    public function withOrder(string $orderNo): self
    {
        $this->options->orderNo = $orderNo;
        $this->options->searchType = SearchType::BY_ORDER_NUMBER;

        return $this;
    }

    /**
     * 透過發票金額查詢 (需帶入 訂單編號 + 發票金額 查詢)
     */
    public function withTotalAmount(int $totalAmount): self
    {
        $this->options->totalAmount = $totalAmount;

        return $this;
    }

    protected function setupSearchRequest(): void
    {
        $this->endpoint = '/Api/invoice_search';
        $this->options->version = '1.3';

        $this->options->orderNo = $this->options->orderNo ?: '';
        $this->options->totalAmount = $this->options->totalAmount ?: 0;
        $this->options->randomNumber = $this->options->randomNumber ?: '';
        $this->options->invoiceNumber = $this->options->invoiceNumber ?: '';
    }

    /**
     * 查詢發票資訊
     */
    public function get(): InvoiceQueryResult
    {
        $this->setupSearchRequest();

        $result = new InvoiceQueryResult($this->sendRequest()->json());

        $this->crypto->verifyCheckCode($result);

        return $result;
    }

    /**
     * 跳轉到 ezPay 平台顯示發票查詢結果
     */
    public function redirectToEZPay(): Response
    {
        $requestData = $this->toRedirectRequestData();

        return $this->formPostSender->send(
            $requestData['url'],
            $requestData['formData']
        );
    }

    /**
     * 跳轉到 ezPay 平台顯示發票查詢結果的請求表單資料
     */
    public function toRedirectRequestData(): array
    {
        $this->setupSearchRequest();

        $this->options->displayFlag = DisplayFlag::WEB_DISPLAY;

        return parent::toRequestData();
    }

    /**
     * 回傳 ezPay 平台顯示發票查詢結果頁面的 URL
     */
    public function getEZPayQueryUrl(): InvoiceQueryUrlResult
    {
        $this->setupSearchRequest();

        $this->options->displayFlag = DisplayFlag::RETURN_URL;

        return new InvoiceQueryUrlResult($this->sendRequest()->json());
    }

    public function setFormPostSender(FormPostSender $formPostSender): self
    {
        $this->formPostSender = $formPostSender;

        return $this;
    }
}
