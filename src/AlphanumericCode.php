<?php

namespace Agriweather\EzpayInvoice;

use Agriweather\EzpayInvoice\Builders\AlphanumericCodeCreateBuilder;
use Agriweather\EzpayInvoice\Builders\AlphanumericCodeQueryBuilder;
use Agriweather\EzpayInvoice\Contracts\HttpSender;
use Agriweather\EzpayInvoice\Crypto\EzpayCrypto;

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
