<?php

namespace Agriweather\EzPayInvoice;

use Agriweather\EzPayInvoice\Contracts\FormPostSender;
use Agriweather\EzPayInvoice\Contracts\HttpSender;
use Agriweather\EzPayInvoice\Crypto\Crypto;

class CrossBorder
{
    public function __construct(
        protected Factory $factory,
        protected Crypto $crypto,
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
