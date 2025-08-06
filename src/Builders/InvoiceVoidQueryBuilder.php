<?php

namespace Agriweather\EzpayInvoice\Builders;

use Agriweather\EzpayInvoice\Options\InvoiceVoidQueryOptions;
use Agriweather\EzpayInvoice\Results\InvoiceVoidResult;

class InvoiceVoidQueryBuilder extends Builder
{
    protected InvoiceVoidQueryOptions $options;

    protected function boot(): void
    {
        $this->crypto->setHashKey($this->factory->config('merchant_hash_key'));
        $this->crypto->setHashIv($this->factory->config('merchant_hash_iv'));

        $this->options = new InvoiceVoidQueryOptions;
        $this->options->merchantId = $this->factory->config('merchant_id');
    }

    public function getOptions(): InvoiceVoidQueryOptions
    {
        return $this->options;
    }

    /**
     * 作廢發票
     *
     * @throws \Agriweather\EzpayInvoice\Exceptions\EzpayInvoiceException
     * @throws \Agriweather\EzpayInvoice\Exceptions\InvalidCheckCodeException
     */
    public function void(string $invoiceNumber, string $reason): InvoiceVoidResult
    {
        $this->endpoint = '/Api/invoice_invalid';

        $this->options->invoiceNumber = $invoiceNumber;
        $this->options->invalidReason = $reason;

        $result = new InvoiceVoidResult($this->sendRequest());

        $this->crypto->verifyCheckCode($result);

        return $result;
    }
}
