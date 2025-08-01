<?php

namespace Agriweather\EzpayInvoice\Builders;

use Agriweather\EzpayInvoice\Crypto\EzpayCrypto;
use Agriweather\EzpayInvoice\Factory;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

abstract class Builder
{
    protected string $endpoint = '';

    protected array $postData = [];

    protected array $items = [];

    protected array $formData = [];

    public function __construct(
        protected Factory $factory,
        protected EzpayCrypto $crypto,
        protected array $config,
        protected string $baseUrl
    ) {
        $this->boot();
    }

    abstract protected function boot(): void;

    protected function sendRequest(): Response
    {
        return Http::asForm()
            ->withUserAgent('ezPay')
            ->post($this->baseUrl.$this->endpoint, $this->formData);
    }
}
