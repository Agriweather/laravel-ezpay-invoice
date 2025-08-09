<?php

namespace Agriweather\EzPayInvoice;

use Agriweather\EzPayInvoice\Builders\AlphanumericCodeCreateBuilder;
use Agriweather\EzPayInvoice\Builders\AlphanumericCodeQueryBuilder;
use Agriweather\EzPayInvoice\Contracts\HttpSender;
use Agriweather\EzPayInvoice\Crypto\EzpayCrypto;

class AlphanumericCode extends SubFactory
{
    public function __construct(
        protected Factory $factory,
        protected EzpayCrypto $crypto,
        protected HttpSender $httpSender
    ) {
        //
    }

    public function create(): AlphanumericCodeCreateBuilder
    {
        return $this->prepareBuilder(new AlphanumericCodeCreateBuilder(
            $this->factory, $this->crypto, $this->httpSender
        ));
    }

    public function query(): AlphanumericCodeQueryBuilder
    {
        return $this->prepareBuilder(new AlphanumericCodeQueryBuilder(
            $this->factory, $this->crypto, $this->httpSender
        ));
    }
}
