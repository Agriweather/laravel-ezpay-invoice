<?php

namespace Agriweather\EzPayInvoice\Builders;

use Agriweather\EzPayInvoice\Contracts\FormPostSender;
use Agriweather\EzPayInvoice\Enums\DisplayFlag;
use Agriweather\EzPayInvoice\Enums\SearchType;
use Agriweather\EzPayInvoice\Options\InvoiceQueryOptions;
use Agriweather\EzPayInvoice\Results\InvoiceQueryResult;
use Agriweather\EzPayInvoice\Results\InvoiceQueryUrlResult;
use Illuminate\Http\Response;
use InvalidArgumentException;

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

        $this->endpoint = '/Api/invoice_search';
    }

    public function getOptions(): InvoiceQueryOptions
    {
        return $this->options;
    }

    /**
     * 透過發票號碼查詢 (需帶入 發票號碼 + 隨機碼 查詢)
     *
     * @throws \InvalidArgumentException
     */
    public function withInvoice(string $invoiceNumber): self
    {
        if (isset($this->options->searchType) &&
            $this->options->searchType !== SearchType::BY_INVOICE_NUMBER
        ) {
            throw new InvalidArgumentException(
                '當前已經使用了其他的查詢方式，無法再使用 發票號碼 + 隨機碼 查詢。請依照文件說明使用。'
            );
        }

        $this->options->invoiceNumber = $invoiceNumber;
        $this->options->searchType = SearchType::BY_INVOICE_NUMBER;

        return $this;
    }

    /**
     * 透過隨機碼查詢 (需帶入 發票號碼 + 隨機碼 查詢)
     *
     * @throws \InvalidArgumentException
     */
    public function withRandomNumber(string $randomNumber): self
    {
        if (isset($this->options->searchType) &&
            $this->options->searchType !== SearchType::BY_INVOICE_NUMBER
        ) {
            throw new InvalidArgumentException(
                '當前已經使用了其他的查詢方式，無法再使用 發票號碼 + 隨機碼 查詢。請依照文件說明使用。'
            );
        }

        $this->options->randomNumber = $randomNumber;
        $this->options->searchType = SearchType::BY_INVOICE_NUMBER;

        return $this;
    }

    /**
     * 透過訂單編號查詢 (需帶入 訂單編號 + 發票金額 查詢)
     *
     * @throws \InvalidArgumentException
     */
    public function withOrder(string $orderNo): self
    {
        if (isset($this->options->searchType) &&
            $this->options->searchType !== SearchType::BY_ORDER_NUMBER
        ) {
            throw new InvalidArgumentException(
                '當前已經使用了其他的查詢方式，無法再使用 訂單編號 + 發票金額 查詢。請依照文件說明使用。'
            );
        }

        $this->options->orderNo = $orderNo;
        $this->options->searchType = SearchType::BY_ORDER_NUMBER;

        return $this;
    }

    /**
     * 透過發票金額查詢 (需帶入 訂單編號 + 發票金額 查詢)
     *
     * @throws \InvalidArgumentException
     */
    public function withTotalAmount(int $totalAmount): self
    {
        if (isset($this->options->searchType) &&
            $this->options->searchType !== SearchType::BY_ORDER_NUMBER
        ) {
            throw new InvalidArgumentException(
                '當前已經使用了其他的查詢方式，無法再使用 訂單編號 + 發票金額 查詢。請依照文件說明使用。'
            );
        }

        $this->options->totalAmount = $totalAmount;
        $this->options->searchType = SearchType::BY_ORDER_NUMBER;

        return $this;
    }

    /**
     * 查詢發票資訊
     *
     * @throws \Agriweather\EzPayInvoice\Exceptions\EzPayInvoiceException
     * @throws \Agriweather\EzPayInvoice\Exceptions\InvalidCheckCodeException
     */
    public function get(): InvoiceQueryResult
    {
        $result = new InvoiceQueryResult($this->sendRequest());

        $this->crypto->verifyCheckCode($result);

        return $result;
    }

    /**
     * 跳轉到 ezPay 平台顯示發票查詢結果
     */
    public function redirectToEzPay(): Response
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
        $this->options->displayFlag = DisplayFlag::WEB_DISPLAY;

        return parent::toRequestData();
    }

    /**
     * 回傳 ezPay 平台顯示發票查詢頁面的 URL
     *
     * @throws \Agriweather\EzPayInvoice\Exceptions\EzPayInvoiceException
     */
    public function getEzPaySearchUrl(): string
    {
        $this->options->displayFlag = DisplayFlag::RETURN_URL;

        return (new InvoiceQueryUrlResult($this->sendRequest()))->url();
    }

    public function setFormPostSender(FormPostSender $formPostSender): self
    {
        $this->formPostSender = $formPostSender;

        return $this;
    }
}
