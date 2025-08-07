<?php

namespace Agriweather\EzpayInvoice\Builders;

use Agriweather\EzpayInvoice\Options\InvoiceInvalidateQueryOptions;
use Agriweather\EzpayInvoice\Results\InvoiceInvalidateResult;

class InvoiceInvalidateQueryBuilder extends Builder
{
    protected InvoiceInvalidateQueryOptions $options;

    protected function boot(): void
    {
        $this->crypto->setHashKey($this->factory->config('merchant_hash_key'));
        $this->crypto->setHashIv($this->factory->config('merchant_hash_iv'));

        $this->options = new InvoiceInvalidateQueryOptions;
        $this->options->merchantId = $this->factory->config('merchant_id');
    }

    public function getOptions(): InvoiceInvalidateQueryOptions
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
     * @throws \Agriweather\EzpayInvoice\Exceptions\EzpayInvoiceException
     */
    public function invalidate(): InvoiceInvalidateResult
    {
        $this->endpoint = '/Api/invoice_invalid';

        return new InvoiceInvalidateResult($this->sendRequest());
    }
}
