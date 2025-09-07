<?php

namespace Agriweather\EzPayInvoice\Resources;

use Agriweather\EzPayInvoice\Builders\CrossBorderInvoice\CreateBuilder;
use Agriweather\EzPayInvoice\Builders\CrossBorderInvoice\InvalidateBuilder;
use Agriweather\EzPayInvoice\Builders\CrossBorderInvoice\QueryBuilder;
use Agriweather\EzPayInvoice\Builders\CrossBorderInvoice\TriggerBuilder;
use Agriweather\EzPayInvoice\Contracts\FormRedirectTransporter;
use Agriweather\EzPayInvoice\Contracts\HttpTransporter;
use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Factory;

class CrossBorderInvoice
{
    use Concerns\PrepareBuilder;

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
