<?php

namespace Agriweather\EzpayInvoice\Senders;

use Agriweather\EzpayInvoice\Contracts\HttpSender as HttpSenderContract;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Response;

class HttpSender implements HttpSenderContract
{
    public function __construct(
        protected Factory $client
    ) {
        //
    }

    public function send(string $url, array $data): Response
    {
        return $this->client
            ->asForm()
            ->withUserAgent('ezPay')
            ->post($url, $data);
    }
}
