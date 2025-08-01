<?php

namespace Agriweather\EzpayInvoice;

use Agriweather\EzpayInvoice\Builders\InvoiceCreateBuilder;
use Agriweather\EzpayInvoice\Builders\InvoiceQueryBuilder;
use Agriweather\EzpayInvoice\Crypto\EzpayCrypto;

class Invoice
{
    public function __construct(
        protected Factory $factory,
        protected EzpayCrypto $crypto,
        protected array $config,
        protected string $baseUrl
    ) {
        //
    }

    public function create(): InvoiceCreateBuilder
    {
        return new InvoiceCreateBuilder(
            $this->factory, $this->crypto, $this->config, $this->baseUrl
        );
    }

    public function query(): InvoiceQueryBuilder
    {
        return new InvoiceQueryBuilder(
            $this->factory, $this->crypto, $this->config, $this->baseUrl
        );
    }
}
