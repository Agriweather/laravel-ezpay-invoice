<?php

namespace Agriweather\EzPayInvoice;

use Agriweather\EzPayInvoice\Builders\InvoiceCreateBuilder;
use Agriweather\EzPayInvoice\Builders\InvoiceInvalidateQueryBuilder;
use Agriweather\EzPayInvoice\Builders\InvoiceQueryBuilder;
use Agriweather\EzPayInvoice\Builders\InvoiceTriggerQueryBuilder;
use Agriweather\EzPayInvoice\Contracts\FormPostSender;
use Agriweather\EzPayInvoice\Contracts\HttpSender;
use Agriweather\EzPayInvoice\Crypto\EzpayCrypto;

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
