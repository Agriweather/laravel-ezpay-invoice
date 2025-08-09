<?php

namespace Agriweather\EzPayInvoice;

use Agriweather\EzPayInvoice\Builders\AllowanceCreateBuilder;
use Agriweather\EzPayInvoice\Builders\AllowanceInvalidateQueryBuilder;
use Agriweather\EzPayInvoice\Builders\AllowanceTriggerQueryBuilder;
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

    public function triggerQuery(): AllowanceTriggerQueryBuilder
    {
        return $this->prepareBuilder(new AllowanceTriggerQueryBuilder(
            $this->factory, $this->crypto, $this->httpSender
        ));
    }

    public function invalidateQuery(): AllowanceInvalidateQueryBuilder
    {
        return $this->prepareBuilder(new AllowanceInvalidateQueryBuilder(
            $this->factory, $this->crypto, $this->httpSender
        ));
    }
}
