<?php

namespace Agriweather\EzPayInvoice\Resources;

use Agriweather\EzPayInvoice\Builders\AlphanumericCode\CreateBuilder;
use Agriweather\EzPayInvoice\Builders\AlphanumericCode\QueryBuilder;
use Agriweather\EzPayInvoice\Contracts\HttpTransporter;
use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Factory;

final class AlphanumericCode
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

    public function query(): QueryBuilder
    {
        return new QueryBuilder(
            $this->factory, $this->crypto, $this->httpTransporter
        );
    }
}
