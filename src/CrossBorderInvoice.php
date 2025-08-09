<?php

namespace Agriweather\EzpayInvoice;

use Agriweather\EzpayInvoice\Builders\CrossBorderInvoiceCreateBuilder;
use Agriweather\EzpayInvoice\Contracts\FormPostSender;
use Agriweather\EzpayInvoice\Contracts\HttpSender;
use Agriweather\EzpayInvoice\Crypto\EzpayCrypto;

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
