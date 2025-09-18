<?php

namespace Agriweather\EzPayInvoice\Resources;

use Agriweather\EzPayInvoice\Builders\AlphanumericCode\CreateBuilder;
use Agriweather\EzPayInvoice\Builders\AlphanumericCode\QueryBuilder;
use Agriweather\EzPayInvoice\Contracts\HttpTransporter;
use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Factory;

final class AlphanumericCode
{
    use Concerns\PrepareBuilder;

    public function __construct(
        private readonly Factory $factory,
        private readonly Crypto $crypto,
        private readonly HttpTransporter $httpTransporter
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
        return $this->prepareBuilder(new QueryBuilder(
            $this->factory, $this->crypto, $this->httpTransporter
        ));
    }
}
