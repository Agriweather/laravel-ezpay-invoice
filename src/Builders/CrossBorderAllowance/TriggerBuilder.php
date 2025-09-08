<?php

namespace Agriweather\EzPayInvoice\Builders\CrossBorderAllowance;

use Agriweather\EzPayInvoice\Builders\Builder;
use Agriweather\EzPayInvoice\Enums\Allowance\TriggerStatus;
use Agriweather\EzPayInvoice\Options\CrossBorderAllowance\TriggerOptions;
use Agriweather\EzPayInvoice\Results\CrossBorderAllowance\TriggerResult;

class TriggerBuilder extends Builder
{
    protected TriggerOptions $options;

    protected function boot(): void
    {
        $this->crypto->setHashKey($this->factory->config('merchant_hash_key'));
        $this->crypto->setHashIv($this->factory->config('merchant_hash_iv'));

        $this->options = new TriggerOptions;
        $this->options->merchantId = $this->factory->config('merchant_id');

        $this->endpoint = '/Api/allowance_touch_issue';
    }

    protected function options(): TriggerOptions
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
     * @param  int|float  $totalAmount  此次折讓的總金額
     */
    public function withTotalAmount(int|float $totalAmount): self
    {
        $this->options->totalAmount = $totalAmount;

        return $this;
    }

    /**
     * 觸發確認折讓
     *
     * @throws \Agriweather\EzPayInvoice\Exceptions\EzPayInvoiceException
     */
    public function confirm(): TriggerResult
    {
        $this->options->status = TriggerStatus::YES;

        return new TriggerResult($this->sendRequest());
    }

    /**
     * 觸發取消折讓
     *
     * @throws \Agriweather\EzPayInvoice\Exceptions\EzPayInvoiceException
     */
    public function cancel(): TriggerResult
    {
        $this->options->status = TriggerStatus::NO;

        return new TriggerResult($this->sendRequest());
    }
}
