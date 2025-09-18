<?php

namespace Agriweather\EzPayInvoice\Resources;

use Agriweather\EzPayInvoice\Contracts\FormRedirectTransporter;
use Agriweather\EzPayInvoice\Contracts\HttpTransporter;
use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Factory;

final class CrossBorder
{
    public function __construct(
        private readonly Factory $factory,
        private readonly Crypto $crypto,
        private readonly HttpTransporter $httpTransporter,
        private readonly FormRedirectTransporter $formRedirectTransporter
    ) {
        //
    }

    public function invoice(): CrossBorderInvoice
    {
        return new CrossBorderInvoice(
            $this->factory, $this->crypto, $this->httpTransporter, $this->formRedirectTransporter
        );
    }

    public function allowance(): CrossBorderAllowance
    {
        return new CrossBorderAllowance(
            $this->factory, $this->crypto, $this->httpTransporter
        );
    }
}
