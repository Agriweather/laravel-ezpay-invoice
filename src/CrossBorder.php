<?php

namespace Agriweather\EzpayInvoice;

use Agriweather\EzpayInvoice\Contracts\FormPostSender;
use Agriweather\EzpayInvoice\Contracts\HttpSender;
use Agriweather\EzpayInvoice\Crypto\EzpayCrypto;

class CrossBorder
{
    public function __construct(
        protected Factory $factory,
        protected EzpayCrypto $crypto,
        protected HttpSender $httpSender,
        protected FormPostSender $formPostSender
    ) {
        //
    }

    public function invoice(): CrossBorderInvoice
    {
        return new CrossBorderInvoice(
            $this->factory, $this->crypto, $this->httpSender, $this->formPostSender
        );
    }
}
