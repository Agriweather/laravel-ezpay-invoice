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
     * 作廢發票
     *
     * @param  string  $invoiceNumber  發票號碼
     * @param  string  $reason  作廢原因，字數限中文 6 字或英文 20 字。
     *
     * @throws \Agriweather\EzpayInvoice\Exceptions\EzpayInvoiceException
     * @throws \Agriweather\EzpayInvoice\Exceptions\InvalidCheckCodeException
     */
    public function invalidate(string $invoiceNumber, string $reason): InvoiceInvalidateResult
    {
        $this->endpoint = '/Api/invoice_invalid';

        $this->options->invoiceNumber = $invoiceNumber;
        $this->options->invalidReason = $reason;

        $result = new InvoiceInvalidateResult($this->sendRequest());

        $this->crypto->verifyCheckCode($result);

        return $result;
    }
}
