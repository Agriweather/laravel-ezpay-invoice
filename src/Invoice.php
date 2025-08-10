<?php

namespace Agriweather\EzPayInvoice;

use Agriweather\EzPayInvoice\Builders\InvoiceCreateBuilder;
use Agriweather\EzPayInvoice\Builders\InvoiceInvalidateBuilder;
use Agriweather\EzPayInvoice\Builders\InvoiceQueryBuilder;
use Agriweather\EzPayInvoice\Builders\InvoiceTriggerBuilder;
use Agriweather\EzPayInvoice\Contracts\FormRedirectTransporter;
use Agriweather\EzPayInvoice\Contracts\HttpTransporter;
use Agriweather\EzPayInvoice\Crypto\Crypto;

class Invoice extends SubFactory
{
    public function __construct(
        protected Factory $factory,
        protected Crypto $crypto,
        protected HttpTransporter $httpTransporter,
        protected FormRedirectTransporter $formRedirectTransporter
    ) {
        //
    }

    public function create(): InvoiceCreateBuilder
    {
        return $this->prepareBuilder(new InvoiceCreateBuilder(
            $this->factory, $this->crypto, $this->httpTransporter
        ));
    }

    public function query(): InvoiceQueryBuilder
    {
        return $this->prepareBuilder((new InvoiceQueryBuilder(
            $this->factory, $this->crypto, $this->httpTransporter
        ))->setFormRedirectTransporter($this->formRedirectTransporter));
    }

    public function pending(): InvoiceTriggerBuilder
    {
        return $this->prepareBuilder(new InvoiceTriggerBuilder(
            $this->factory, $this->crypto, $this->httpTransporter
        ));
    }

    public function voidable(): InvoiceInvalidateBuilder
    {
        return $this->prepareBuilder(new InvoiceInvalidateBuilder(
            $this->factory, $this->crypto, $this->httpTransporter
        ));
    }
}
