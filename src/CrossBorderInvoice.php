<?php

namespace Agriweather\EzPayInvoice;

use Agriweather\EzPayInvoice\Builders\CrossBorderInvoiceCreateBuilder;
use Agriweather\EzPayInvoice\Contracts\FormPostSender;
use Agriweather\EzPayInvoice\Contracts\HttpSender;
use Agriweather\EzPayInvoice\Crypto\Crypto;

class CrossBorderInvoice extends SubFactory
{
    public function __construct(
        protected Factory $factory,
        protected Crypto $crypto,
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
