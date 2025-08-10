<?php

namespace Agriweather\EzPayInvoice;

use Agriweather\EzPayInvoice\Builders\AlphanumericCodeCreateBuilder;
use Agriweather\EzPayInvoice\Builders\AlphanumericCodeQueryBuilder;
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

    public function create(): AlphanumericCodeCreateBuilder
    {
        return $this->prepareBuilder(new AlphanumericCodeCreateBuilder(
            $this->factory, $this->crypto, $this->httpTransporter
        ));
    }

    public function query(): AlphanumericCodeQueryBuilder
    {
        return $this->prepareBuilder(new AlphanumericCodeQueryBuilder(
            $this->factory, $this->crypto, $this->httpTransporter
        ));
    }
}
