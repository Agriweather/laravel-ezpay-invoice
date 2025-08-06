<?php

namespace Agriweather\EzpayInvoice;

use Agriweather\EzpayInvoice\Builders\AlphanumericCodeCreateBuilder;
use Agriweather\EzpayInvoice\Builders\AlphanumericCodeQueryBuilder;
use Agriweather\EzpayInvoice\Contracts\HttpSender;
use Agriweather\EzpayInvoice\Crypto\EzpayCrypto;

class AlphanumericCode
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
        return new AlphanumericCodeCreateBuilder(
            $this->factory, $this->crypto, $this->httpSender
        );
    }

    public function query(): AlphanumericCodeQueryBuilder
    {
        return new AlphanumericCodeQueryBuilder(
            $this->factory, $this->crypto, $this->httpSender
        );
    }
}
