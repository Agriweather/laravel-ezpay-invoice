<?php

namespace Agriweather\EzpayInvoice;

use Agriweather\EzpayInvoice\Builders\InvoiceCreateBuilder;
use Agriweather\EzpayInvoice\Builders\InvoiceQueryBuilder;
use Agriweather\EzpayInvoice\Builders\InvoiceTriggerQueryBuilder;
use Agriweather\EzpayInvoice\Builders\InvoiceVoidQueryBuilder;
use Agriweather\EzpayInvoice\Contracts\FormPostSender;
use Agriweather\EzpayInvoice\Contracts\HttpSender;
use Agriweather\EzpayInvoice\Crypto\EzpayCrypto;

class Invoice
{
    public function __construct(
        protected Factory $factory,
        protected EzpayCrypto $crypto,
        protected HttpSender $httpSender,
        protected FormPostSender $formPostSender
    ) {
        //
    }

    public function create(): InvoiceCreateBuilder
    {
        return new InvoiceCreateBuilder(
            $this->factory, $this->crypto, $this->httpSender
        );
    }

    public function query(): InvoiceQueryBuilder
    {
        return (new InvoiceQueryBuilder(
            $this->factory, $this->crypto, $this->httpSender
        ))->setFormPostSender($this->formPostSender);
    }

    public function triggerQuery(): InvoiceTriggerQueryBuilder
    {
        return new InvoiceTriggerQueryBuilder(
            $this->factory, $this->crypto, $this->httpSender
        );
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
    public function void(string $invoiceNumber, string $reason)
    {
        return (new InvoiceVoidQueryBuilder(
            $this->factory, $this->crypto, $this->httpSender
        ))->void($invoiceNumber, $reason);
    }
}
