<?php

namespace Agriweather\EzPayInvoice;

use Agriweather\EzPayInvoice\Builders\CrossBorderInvoiceCreateBuilder;
use Agriweather\EzPayInvoice\Contracts\FormRedirectTransporter;
use Agriweather\EzPayInvoice\Contracts\HttpTransporter;
use Agriweather\EzPayInvoice\Crypto\Crypto;

class CrossBorderInvoice extends SubFactory
{
    public function __construct(
        protected Factory $factory,
        protected Crypto $crypto,
        protected HttpTransporter $httpTransporter,
        protected FormRedirectTransporter $formRedirectTransporter
    ) {
        //
    }

    public function create(): CrossBorderInvoiceCreateBuilder
    {
        return $this->prepareBuilder(new CrossBorderInvoiceCreateBuilder(
            $this->factory, $this->crypto, $this->httpTransporter
        ));
    }
}
