<?php

namespace Agriweather\EzPayInvoice\Builders;

use Agriweather\EzPayInvoice\Contracts\HttpSender;
use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Exceptions\EzPayInvoiceException;
use Agriweather\EzPayInvoice\Factory;
use Agriweather\EzPayInvoice\Options\Options;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Tappable;

abstract class Builder
{
    use Concerns\HasTransformOptions;
    use Conditionable;
    use Tappable;

    protected string $endpoint = '';

    public function __construct(
        protected Factory $factory,
        protected Crypto $crypto,
        protected HttpSender $httpSender
    ) {
        $this->boot();
    }

    abstract protected function boot(): void;

    abstract public function getOptions(): Options;

    /**
     * 取得 HTTP 請求數據
     */
    public function toRequestData(): array
    {
        $url = $this->factory->baseUrl().$this->endpoint;
        $options = $this->getOptions();

        if ($this->transformOptionsCallback) {
            $options = call_user_func($this->transformOptionsCallback, $options);
        }

        $formData = $options->toArray();

        // 如果有 PostData_ 則進行加密
        if (isset($formData['PostData_'])) {
            $formData['PostData_'] = $this->crypto->encryptByAES(
                $formData['PostData_']
            );
        }

        return [
            'url' => $url,
            'formData' => $formData,
        ];
    }

    /**
     * 發送 API 請求到 ezPay 發票平台
     *
     * @throws \Agriweather\EzPayInvoice\Exceptions\EzPayInvoiceException
     */
    protected function sendRequest(): array
    {
        $requestData = $this->toRequestData();

        $response = $this->httpSender->send(
            $requestData['url'],
            $requestData['formData']
        );

        $data = $response->json();

        if ($data['Status'] !== 'SUCCESS') {
            throw new EzPayInvoiceException(
                $requestData['url'], $requestData['formData'], $data['Status'], $data['Message']
            );
        }

        return $data;
    }
}
