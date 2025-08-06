<?php

namespace Agriweather\EzpayInvoice;

use Agriweather\EzpayInvoice\Builders\InvoiceCreateBuilder;
use Agriweather\EzpayInvoice\Builders\InvoiceQueryBuilder;
use Agriweather\EzpayInvoice\Contracts\HttpSender;
use Agriweather\EzpayInvoice\Crypto\EzpayCrypto;

class Invoice
{
    public function __construct(
        protected Factory $factory,
        protected EzpayCrypto $crypto,
        protected HttpSender $httpSender
    ) {
        //
    }

    public function create(): InvoiceCreateBuilder
    {
        return new InvoiceCreateBuilder(
            $this->factory, $this->crypto, $this->httpSender
        );
    }

    public function query(): InvoiceQueryBuilder
    {
        return new InvoiceQueryBuilder(
            $this->factory, $this->crypto, $this->httpSender
        );
    }
}
