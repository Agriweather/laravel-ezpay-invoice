<?php

namespace Agriweather\EzPayInvoice\Builders\Invoice;

use Agriweather\EzPayInvoice\Attributes\Resource;
use Agriweather\EzPayInvoice\Builders\Builder;
use Agriweather\EzPayInvoice\Contracts\FormRedirectTransporter;
use Agriweather\EzPayInvoice\Enums\Invoice\DisplayFlag;
use Agriweather\EzPayInvoice\Enums\Invoice\SearchType;
use Agriweather\EzPayInvoice\Options\Invoice\QueryOptions;
use Agriweather\EzPayInvoice\Resources\Invoice;
use Agriweather\EzPayInvoice\Results\Invoice\QueryResult;
use Agriweather\EzPayInvoice\Results\Invoice\UrlQueryResult;
use Illuminate\Http\Response;
use InvalidArgumentException;

#[Resource(Invoice::class, 'query')]
class QueryBuilder extends Builder
{
    protected QueryOptions $options;

    protected FormRedirectTransporter $formRedirectTransporter;

    protected function boot(): void
    {
        $this->crypto->setHashKey($this->factory->config('merchant_hash_key'));
        $this->crypto->setHashIv($this->factory->config('merchant_hash_iv'));

        $this->options = new QueryOptions;
        $this->options->merchantId = $this->factory->config('merchant_id');

        $this->endpoint = '/Api/invoice_search';
    }

    protected function options(): QueryOptions
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
    public function get(): QueryResult
    {
        $result = new QueryResult($this->sendRequest());

        $this->crypto->verifyCheckCode($result, ! $this->factory->recording());

        return $result;
    }

    /**
     * 跳轉到 ezPay 平台顯示發票查詢結果
     */
    public function redirectToEzPay(): Response
    {
        $requestData = $this->toRedirectRequestData();

        return $this->formRedirectTransporter->send(
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

        return (new UrlQueryResult($this->sendRequest()))->url();
    }

    public function setFormRedirectTransporter(FormRedirectTransporter $formRedirectTransporter): self
    {
        $this->formRedirectTransporter = $formRedirectTransporter;

        return $this;
    }
}
