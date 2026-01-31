<?php

namespace Agriweather\EzPayInvoice\Resources;

use Agriweather\EzPayInvoice\Builders\Allowance\CreateBuilder;
use Agriweather\EzPayInvoice\Builders\Allowance\InvalidateBuilder;
use Agriweather\EzPayInvoice\Builders\Allowance\TriggerBuilder;
use Agriweather\EzPayInvoice\Contracts\HttpTransporter;
use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Factory;

final class Allowance
{
    public function __construct(
        private readonly Factory $factory,
        private readonly Crypto $crypto,
        private readonly HttpTransporter $httpTransporter
    ) {
        //
    }

    public function create(): CreateBuilder
    {
        return new CreateBuilder(
            $this->factory, $this->crypto, $this->httpTransporter
        );
    }

    public function pending(): TriggerBuilder
    {
        return new TriggerBuilder(
            $this->factory, $this->crypto, $this->httpTransporter
        );
    }

    public function voidable(): InvalidateBuilder
    {
        return new InvalidateBuilder(
            $this->factory, $this->crypto, $this->httpTransporter
        );
    }
}
