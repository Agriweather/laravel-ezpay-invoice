<?php

namespace Agriweather\EzPayInvoice\Transporters;

use Agriweather\EzPayInvoice\Contracts\HttpTransporter as HttpTransporterContract;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Response as ClientResponse;

class HttpTransporter implements HttpTransporterContract
{
    protected int $timeout = 30;

    public function __construct(
        protected Factory $client
    ) {
        //
    }

    public function setTimeout(int $seconds): static
    {
        $this->timeout = $seconds;

        return $this;
    }

    public function send(string $url, array $data): ClientResponse
    {
        return $this->client
            ->asForm()
            ->withUserAgent('ezPay')
            ->timeout($this->timeout)
            ->post($url, $data);
    }
}
