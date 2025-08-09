<?php

namespace Agriweather\EzPayInvoice;

use Agriweather\EzPayInvoice\Builders\CrossBorderInvoiceCreateBuilder;
use Agriweather\EzPayInvoice\Contracts\FormPostSender;
use Agriweather\EzPayInvoice\Contracts\HttpSender;
use Agriweather\EzPayInvoice\Crypto\EzpayCrypto;

class CrossBorderInvoice extends SubFactory
{
    public function __construct(
        protected Factory $factory,
        protected EzpayCrypto $crypto,
        protected HttpSender $httpSender,
        protected FormPostSender $formPostSender
    ) {
        //
    }

    public function create(): CrossBorderInvoiceCreateBuilder
    {
        return $this->prepareBuilder(new CrossBorderInvoiceCreateBuilder(
            $this->factory, $this->crypto, $this->httpSender
        ));
    }
}
