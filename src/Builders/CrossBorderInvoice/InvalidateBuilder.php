<?php

namespace Agriweather\EzPayInvoice\Builders\CrossBorderInvoice;

use Agriweather\EzPayInvoice\Attributes\Resource;
use Agriweather\EzPayInvoice\Builders\Builder;
use Agriweather\EzPayInvoice\Options\CrossBorderInvoice\InvalidateOptions;
use Agriweather\EzPayInvoice\Resources\CrossBorderInvoice;
use Agriweather\EzPayInvoice\Results\CrossBorderInvoice\InvalidateResult;

#[Resource(CrossBorderInvoice::class, 'voidable')]
class InvalidateBuilder extends Builder
{
    protected InvalidateOptions $options;

    protected function boot(): void
    {
        $this->crypto->setHashKey($this->factory->config('merchant_hash_key'));
        $this->crypto->setHashIv($this->factory->config('merchant_hash_iv'));

        $this->options = new InvalidateOptions;
        $this->options->merchantId = $this->factory->config('merchant_id');

        $this->endpoint = '/Api/invoice_invalid';
    }

    protected function options(): InvalidateOptions
    {
        return $this->options;
    }

    /**
     * 發票號碼
     *
     * @param  string  $invoiceNumber  發票號碼
     */
    public function withInvoice(string $invoiceNumber): self
    {
        $this->options->invoiceNumber = $invoiceNumber;

        return $this;
    }

    /**
     * 作廢原因
     *
     * @param  string  $invalidReason  作廢原因，字數限中文 6 字或英文 20 字。
     */
    public function because(string $invalidReason): self
    {
        $this->options->invalidReason = $invalidReason;

        return $this;
    }

    /**
     * 作廢發票
     *
     * @throws \Agriweather\EzPayInvoice\Exceptions\EzPayInvoiceException
     */
    public function invalidate(): InvalidateResult
    {
        return new InvalidateResult($this->sendRequest());
    }
}
