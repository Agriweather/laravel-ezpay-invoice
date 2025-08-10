<?php

namespace Agriweather\EzPayInvoice\Transporters;

use Agriweather\EzPayInvoice\Contracts\HttpTransporter as HttpTransporterContract;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Response as ClientResponse;

class HttpTransporter implements HttpTransporterContract
{
    public function __construct(
        protected Factory $client
    ) {
        //
    }

    public function send(string $url, array $data): ClientResponse
    {
        return $this->client
            ->asForm()
            ->withUserAgent('ezPay')
            ->post($url, $data);
    }
}
