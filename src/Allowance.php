<?php

namespace Agriweather\EzPayInvoice;

use Agriweather\EzPayInvoice\Builders\Allowance\CreateBuilder;
use Agriweather\EzPayInvoice\Builders\Allowance\InvalidateBuilder;
use Agriweather\EzPayInvoice\Builders\Allowance\TriggerBuilder;
use Agriweather\EzPayInvoice\Contracts\HttpTransporter;
use Agriweather\EzPayInvoice\Crypto\Crypto;

class Allowance extends SubFactory
{
    public function __construct(
        protected Factory $factory,
        protected Crypto $crypto,
        protected HttpTransporter $httpTransporter
    ) {
        //
    }

    public function create(): CreateBuilder
    {
        return $this->prepareBuilder(new CreateBuilder(
            $this->factory, $this->crypto, $this->httpTransporter
        ));
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
