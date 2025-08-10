<?php

namespace Agriweather\EzPayInvoice\Builders;

use Agriweather\EzPayInvoice\Enums\AllowanceTriggerStatus;
use Agriweather\EzPayInvoice\Options\AllowanceTriggerOptions;
use Agriweather\EzPayInvoice\Results\AllowanceTriggerResult;

class AllowanceTriggerBuilder extends Builder
{
    protected AllowanceTriggerOptions $options;

    protected function boot(): void
    {
        $this->crypto->setHashKey($this->factory->config('merchant_hash_key'));
        $this->crypto->setHashIv($this->factory->config('merchant_hash_iv'));

        $this->options = new AllowanceTriggerOptions;
        $this->options->merchantId = $this->factory->config('merchant_id');

        $this->endpoint = '/Api/allowance_touch_issue';
    }

    protected function options(): AllowanceTriggerOptions
    {
        return $this->options;
    }

    /**
     * 折讓號
     *
     * @param  string  $allowanceNo  開立折讓時的折讓號
     */
    public function withAllowance(string $allowanceNo): self
    {
        $this->options->allowanceNo = $allowanceNo;

        return $this;
    }

    /**
     * 商店自訂訂單編號
     *
     * @param  string  $orderNo  此次開立折讓的發票，於開立發票時，提供之自訂編號。
     */
    public function withOrder(string $orderNo): self
    {
        $this->options->orderNo = $orderNo;

        return $this;
    }

    /**
     * 折讓總金額
     *
     * @param  int  $totalAmount  此次折讓的總金額
     */
    public function withTotalAmount(int $totalAmount): self
    {
        $this->options->totalAmount = $totalAmount;

        return $this;
    }

    /**
     * 觸發確認折讓
     *
     * @throws \Agriweather\EzPayInvoice\Exceptions\EzPayInvoiceException
     */
    public function confirm(): AllowanceTriggerResult
    {
        $this->options->status = AllowanceTriggerStatus::YES;

        return new AllowanceTriggerResult($this->sendRequest());
    }

    /**
     * 觸發取消折讓
     *
     * @throws \Agriweather\EzPayInvoice\Exceptions\EzPayInvoiceException
     */
    public function cancel(): AllowanceTriggerResult
    {
        $this->options->status = AllowanceTriggerStatus::NO;

        return new AllowanceTriggerResult($this->sendRequest());
    }
}
