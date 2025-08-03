<?php

namespace Agriweather\EzpayInvoice;

use Agriweather\EzpayInvoice\Crypto\EzpayCrypto;

class Factory
{
    protected string $productionBaseUrl = 'https://inv.ezpay.com.tw';

    protected string $testingBaseUrl = 'https://cinv.ezpay.com.tw';

    public function __construct(
        protected EzpayCrypto $crypto,
        protected array $config
    ) {
        //
    }

    public function invoice(): Invoice
    {
        return new Invoice(
            $this, $this->crypto, $this->config, $this->baseUrl()
        );
    }

    protected function baseUrl()
    {
        return $this->config['env'] === 'production'
            ? $this->productionBaseUrl
            : $this->testingBaseUrl;
    }
}
