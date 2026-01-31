<?php

namespace Agriweather\EzPayInvoice\Resources;

use Agriweather\EzPayInvoice\Builders\Invoice\CreateBuilder;
use Agriweather\EzPayInvoice\Builders\Invoice\InvalidateBuilder;
use Agriweather\EzPayInvoice\Builders\Invoice\QueryBuilder;
use Agriweather\EzPayInvoice\Builders\Invoice\TriggerBuilder;
use Agriweather\EzPayInvoice\Contracts\FormRedirectTransporter;
use Agriweather\EzPayInvoice\Contracts\HttpTransporter;
use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Factory;

final class Invoice
{
    public function __construct(
        private readonly Factory $factory,
        private readonly Crypto $crypto,
        private readonly HttpTransporter $httpTransporter,
        private readonly FormRedirectTransporter $formRedirectTransporter
    ) {
        //
    }

    public function create(): CreateBuilder
    {
        return new CreateBuilder(
            $this->factory, $this->crypto, $this->httpTransporter
        );
    }

    public function query(): QueryBuilder
    {
        return (new QueryBuilder(
            $this->factory, $this->crypto, $this->httpTransporter
        ))->setFormRedirectTransporter($this->formRedirectTransporter);
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
