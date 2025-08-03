<?php

namespace Agriweather\EzpayInvoice;

use Agriweather\EzpayInvoice\Builders\InvoiceCreateBuilder;
use Agriweather\EzpayInvoice\Builders\InvoiceQueryBuilder;
use Agriweather\EzpayInvoice\Crypto\EzpayCrypto;
use Illuminate\Http\Client\Factory as HttpClient;

class Invoice
{
    public function __construct(
        protected HttpClient $client,
        protected Factory $factory,
        protected EzpayCrypto $crypto
    ) {
        //
    }

    public function create(): InvoiceCreateBuilder
    {
        return new InvoiceCreateBuilder(
            $this->client, $this->factory, $this->crypto
        );
    }

    public function query(): InvoiceQueryBuilder
    {
        return new InvoiceQueryBuilder(
            $this->client, $this->factory, $this->crypto
        );
    }
}
