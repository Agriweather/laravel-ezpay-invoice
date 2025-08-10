<?php

namespace Agriweather\EzPayInvoice;

use Agriweather\EzPayInvoice\Builders\AlphanumericCode\CreateBuilder;
use Agriweather\EzPayInvoice\Builders\AlphanumericCode\QueryBuilder;
use Agriweather\EzPayInvoice\Contracts\HttpTransporter;
use Agriweather\EzPayInvoice\Crypto\Crypto;

class AlphanumericCode extends SubFactory
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

    public function query(): QueryBuilder
    {
        return $this->prepareBuilder(new QueryBuilder(
            $this->factory, $this->crypto, $this->httpTransporter
        ));
    }
}
