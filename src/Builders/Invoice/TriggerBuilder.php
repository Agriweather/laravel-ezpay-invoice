<?php

namespace Agriweather\EzPayInvoice\Builders\Invoice;

use Agriweather\EzPayInvoice\Attributes\Resource;
use Agriweather\EzPayInvoice\Builders\Builder;
use Agriweather\EzPayInvoice\Options\Invoice\TriggerOptions;
use Agriweather\EzPayInvoice\Resources\Invoice;
use Agriweather\EzPayInvoice\Results\Invoice\TriggerResult;

#[Resource(Invoice::class, 'pending')]
class TriggerBuilder extends Builder
{
    protected TriggerOptions $options;

    protected function boot(): void
    {
        $this->crypto->setHashKey($this->factory->config('merchant_hash_key'));
        $this->crypto->setHashIv($this->factory->config('merchant_hash_iv'));

        $this->options = new TriggerOptions;
        $this->options->merchantId = $this->factory->config('merchant_id');

        $this->endpoint = '/Api/invoice_touch_issue';
    }

    protected function options(): TriggerOptions
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
     * @throws \RuntimeException
     * @throws \Agriweather\EzPayInvoice\Exceptions\EzPayInvoiceException
     * @throws \Agriweather\EzPayInvoice\Exceptions\InvalidCheckCodeException
     */
    public function trigger(): TriggerResult
    {
        $result = new TriggerResult($this->sendRequest());

        if (! $this->factory->recording()) {
            $this->crypto->verifyCheckCode($result);
        }

        return $result;
    }
}
