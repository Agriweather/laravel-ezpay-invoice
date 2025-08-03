<?php

namespace Agriweather\EzpayInvoice\Builders;

use Agriweather\EzpayInvoice\Crypto\EzpayCrypto;
use Agriweather\EzpayInvoice\Factory;
use Agriweather\EzpayInvoice\Options\Options;
use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Tappable;

abstract class Builder
{
    use Conditionable;
    use Tappable;

    protected string $endpoint = '';

    public function __construct(
        protected HttpClient $client,
        protected Factory $factory,
        protected EzpayCrypto $crypto
    ) {
        $this->boot();
    }

    abstract protected function boot(): void;

    abstract public function getOptions(): Options;

    protected function sendRequest(): Response
    {
        $url = $this->factory->baseUrl().$this->endpoint;
        $formData = $this->getOptions()->toArray();

        // 如果有 PostData_ 則進行加密
        if (isset($formData['PostData_'])) {
            $formData['PostData_'] = $this->crypto->encryptPostData(
                $formData['PostData_']
            );
        }

        return $this->client
            ->asForm()
            ->withUserAgent('ezPay')
            ->post($url, $formData);
    }
}
