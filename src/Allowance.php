<?php

namespace Agriweather\EzpayInvoice;

use Agriweather\EzpayInvoice\Builders\AllowanceCreateBuilder;
use Agriweather\EzpayInvoice\Builders\AllowanceInvalidateQueryBuilder;
use Agriweather\EzpayInvoice\Builders\AllowanceTriggerQueryBuilder;
use Agriweather\EzpayInvoice\Contracts\HttpSender;
use Agriweather\EzpayInvoice\Crypto\EzpayCrypto;

class Allowance extends SubFactory
{
    public function __construct(
        protected Factory $factory,
        protected EzpayCrypto $crypto,
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
