<?php

namespace Agriweather\EzpayInvoice\Builders;

use Agriweather\EzpayInvoice\Contracts\HttpSender;
use Agriweather\EzpayInvoice\Crypto\EzpayCrypto;
use Agriweather\EzpayInvoice\Factory;
use Agriweather\EzpayInvoice\Options\Options;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Tappable;

abstract class Builder
{
    use Conditionable;
    use Tappable;

    protected string $endpoint = '';

    public function __construct(
        protected Factory $factory,
        protected EzpayCrypto $crypto,
        protected HttpSender $httpSender
    ) {
        $this->boot();
    }

    abstract protected function boot(): void;

    abstract public function getOptions(): Options;

    public function toRequestData(): array
    {
        $url = $this->factory->baseUrl().$this->endpoint;
        $formData = $this->getOptions()->toArray();

        // 如果有 PostData_ 則進行加密
        if (isset($formData['PostData_'])) {
            $formData['PostData_'] = $this->crypto->encryptPostData(
                $formData['PostData_']
            );
        }

        return [
            'url' => $url,
            'formData' => $formData,
        ];
    }

    protected function sendRequest(): ClientResponse
    {
        $requestData = $this->toRequestData();

        return $this->httpSender->send(
            $requestData['url'],
            $requestData['formData']
        );
    }
}
