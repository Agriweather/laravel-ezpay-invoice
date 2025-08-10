<?php

namespace Agriweather\EzPayInvoice;

use Agriweather\EzPayInvoice\Contracts\FormRedirectTransporter;
use Agriweather\EzPayInvoice\Contracts\HttpTransporter;
use Agriweather\EzPayInvoice\Crypto\Crypto;

class CrossBorder
{
    public function __construct(
        protected Factory $factory,
        protected Crypto $crypto,
        protected HttpTransporter $httpTransporter,
        protected FormRedirectTransporter $formRedirectTransporter
    ) {
        //
    }

    public function invoice(): CrossBorderInvoice
    {
        return new CrossBorderInvoice(
            $this->factory, $this->crypto, $this->httpTransporter, $this->formRedirectTransporter
        );
    }
}
