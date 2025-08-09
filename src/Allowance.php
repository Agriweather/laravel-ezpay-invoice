<?php

namespace Agriweather\EzPayInvoice;

use Agriweather\EzPayInvoice\Builders\AllowanceCreateBuilder;
use Agriweather\EzPayInvoice\Builders\AllowanceInvalidateBuilder;
use Agriweather\EzPayInvoice\Builders\AllowanceTriggerBuilder;
use Agriweather\EzPayInvoice\Contracts\HttpSender;
use Agriweather\EzPayInvoice\Crypto\Crypto;

class Allowance extends SubFactory
{
    public function __construct(
        protected Factory $factory,
        protected Crypto $crypto,
        protected HttpSender $httpSender
    ) {
        //
    }

    public function create(): AllowanceCreateBuilder
    {
        return $this->prepareBuilder(new AllowanceCreateBuilder(
            $this->factory, $this->crypto, $this->httpSender
        ));
    }

    public function pending(): AllowanceTriggerBuilder
    {
        return $this->prepareBuilder(new AllowanceTriggerBuilder(
            $this->factory, $this->crypto, $this->httpSender
        ));
    }

    public function voidable(): AllowanceInvalidateBuilder
    {
        return $this->prepareBuilder(new AllowanceInvalidateBuilder(
            $this->factory, $this->crypto, $this->httpSender
        ));
    }
}
