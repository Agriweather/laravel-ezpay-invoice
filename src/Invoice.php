<?php

namespace Agriweather\EzpayInvoice;

use Agriweather\EzpayInvoice\Builders\InvoiceCreateBuilder;
use Agriweather\EzpayInvoice\Builders\InvoiceQueryBuilder;
use Agriweather\EzpayInvoice\Crypto\EzpayCrypto;

class Invoice
{
    public function __construct(
        protected Factory $factory,
        protected EzpayCrypto $crypto
    ) {
        //
    }

    public function create(): InvoiceCreateBuilder
    {
        return new InvoiceCreateBuilder(
            $this->factory, $this->crypto
        );
    }

    public function query(): InvoiceQueryBuilder
    {
        return new InvoiceQueryBuilder(
            $this->factory, $this->crypto
        );
    }
}
