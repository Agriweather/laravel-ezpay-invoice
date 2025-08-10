<?php

namespace Agriweather\EzPayInvoice;

use Agriweather\EzPayInvoice\Builders\Invoice\CreateBuilder;
use Agriweather\EzPayInvoice\Builders\Invoice\InvalidateBuilder;
use Agriweather\EzPayInvoice\Builders\Invoice\QueryBuilder;
use Agriweather\EzPayInvoice\Builders\Invoice\TriggerBuilder;
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

    public function create(): CreateBuilder
    {
        return $this->prepareBuilder(new CreateBuilder(
            $this->factory, $this->crypto, $this->httpTransporter
        ));
    }

    public function query(): QueryBuilder
    {
        return $this->prepareBuilder((new QueryBuilder(
            $this->factory, $this->crypto, $this->httpTransporter
        ))->setFormRedirectTransporter($this->formRedirectTransporter));
    }

    public function pending(): TriggerBuilder
    {
        return $this->prepareBuilder(new TriggerBuilder(
            $this->factory, $this->crypto, $this->httpTransporter
        ));
    }

    public function voidable(): InvalidateBuilder
    {
        return $this->prepareBuilder(new InvalidateBuilder(
            $this->factory, $this->crypto, $this->httpTransporter
        ));
    }
}
