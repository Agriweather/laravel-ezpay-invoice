<?php

namespace Agriweather\EzPayInvoice\Builders;

use Agriweather\EzPayInvoice\Options\InvoiceTriggerQueryOptions;
use Agriweather\EzPayInvoice\Results\InvoiceTriggerResult;

class InvoiceTriggerQueryBuilder extends Builder
{
    protected InvoiceTriggerQueryOptions $options;

    protected function boot(): void
    {
        $this->crypto->setHashKey($this->factory->config('merchant_hash_key'));
        $this->crypto->setHashIv($this->factory->config('merchant_hash_iv'));

        $this->options = new InvoiceTriggerQueryOptions;
        $this->options->merchantId = $this->factory->config('merchant_id');
    }

    public function getOptions(): InvoiceTriggerQueryOptions
    {
        return $this->options;
    }

    /**
     * ezPay 平台交易序號
     *
     * 若商店同時使用 ezPay 簡單付金流服務，請於此參數傳送 ezPay 交易序號，
     * 以便對應金流交易開立發票；未使用者則不需輸入。
     */
    public function withEzPayTransNumber(string $transNumber): self
    {
        $this->options->ezPayTransNumber = $transNumber;

        return $this;
    }

    /**
     * ezPay 電子發票開立序號
     *
     * 開立發票時的 ezPay 電子發票開立序號。
     */
    public function withInvoiceTransNo(string $invoiceTransNo): self
    {
        $this->options->invoiceTransNo = $invoiceTransNo;

        return $this;
    }

    /**
     * 商店自訂訂單編號
     *
     * @param  string  $orderNo  商店自訂訂單編號，限英、數字、_ 格式。同一商店中此編號不可重覆。
     */
    public function withOrder(string $orderNo): self
    {
        $this->options->orderNo = $orderNo;

        return $this;
    }

    /**
     * 發票金額
     */
    public function withTotalAmount(int $totalAmount): self
    {
        $this->options->totalAmount = $totalAmount;

        return $this;
    }

    /**
     * 觸發開立發票
     *
     * @throws \Agriweather\EzPayInvoice\Exceptions\EzPayInvoiceException
     * @throws \Agriweather\EzPayInvoice\Exceptions\InvalidCheckCodeException
     */
    public function trigger(): InvoiceTriggerResult
    {
        $this->endpoint = '/Api/invoice_touch_issue';

        $result = new InvoiceTriggerResult($this->sendRequest());

        $this->crypto->verifyCheckCode($result);

        return $result;
    }
}
