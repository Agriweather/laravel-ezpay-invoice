<?php

namespace Agriweather\EzpayInvoice;

use Agriweather\EzpayInvoice\Builders\InvoiceCreateBuilder;
use Agriweather\EzpayInvoice\Builders\InvoiceInvalidateQueryBuilder;
use Agriweather\EzpayInvoice\Builders\InvoiceQueryBuilder;
use Agriweather\EzpayInvoice\Builders\InvoiceTriggerQueryBuilder;
use Agriweather\EzpayInvoice\Contracts\FormPostSender;
use Agriweather\EzpayInvoice\Contracts\HttpSender;
use Agriweather\EzpayInvoice\Crypto\EzpayCrypto;

class Invoice extends SubFactory
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
        return $this->prepareBuilder(new InvoiceCreateBuilder(
            $this->factory, $this->crypto, $this->httpSender
        ));
    }

    public function query(): InvoiceQueryBuilder
    {
        return $this->prepareBuilder((new InvoiceQueryBuilder(
            $this->factory, $this->crypto, $this->httpSender
        ))->setFormPostSender($this->formPostSender));
    }

    public function triggerQuery(): InvoiceTriggerQueryBuilder
    {
        return $this->prepareBuilder(new InvoiceTriggerQueryBuilder(
            $this->factory, $this->crypto, $this->httpSender
        ));
    }

    public function invalidateQuery(): InvoiceInvalidateQueryBuilder
    {
        return $this->prepareBuilder(new InvoiceInvalidateQueryBuilder(
            $this->factory, $this->crypto, $this->httpSender
        ));
    }
}
