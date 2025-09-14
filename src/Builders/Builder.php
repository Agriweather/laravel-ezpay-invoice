<?php

namespace Agriweather\EzPayInvoice\Builders;

use Agriweather\EzPayInvoice\Attributes\Resource;
use Agriweather\EzPayInvoice\Contracts\HttpTransporter;
use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Exceptions\EzPayInvoiceException;
use Agriweather\EzPayInvoice\Factory;
use Agriweather\EzPayInvoice\Options\Options;
use Agriweather\EzPayInvoice\Results\Result;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Tappable;

abstract class Builder
{
    use Concerns\Dumpable;
    use Concerns\HasTransformOptions;
    use Conditionable;
    use Tappable;

    protected string $endpoint = '';

    private ?Options $configuredOptions = null;

    public function __construct(
        protected Factory $factory,
        protected Crypto $crypto,
        protected HttpTransporter $httpTransporter
    ) {
        $this->boot();
    }

    abstract protected function boot(): void;

    abstract protected function options(): Options;

    /**
     * 發送 API 請求到 ezPay 發票平台
     *
     * @throws \Agriweather\EzPayInvoice\Exceptions\EzPayInvoiceException
     */
    protected function sendRequest(): array
    {
        $requestData = $this->toRequestData();

        // 如果有設定 fake 的回應結果，則直接回傳
        if ($result = $this->record()) {
            return $result->toArray();
        }

        $this->httpTransporter->setTimeout($this->factory->config('timeout'));

        $response = $this->httpTransporter->send(
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

    /**
     * 取得 HTTP 請求數據
     */
    public function toRequestData(): array
    {
        $url = $this->factory->baseUrl().$this->endpoint;
        $options = $this->getConfiguredOptions();
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

    protected function getConfiguredOptions(): Options
    {
        if ($this->configuredOptions) {
            return $this->configuredOptions;
        }

        $options = $this->options();

        if ($this->transformOptionsCallback) {
            $options = call_user_func($this->transformOptionsCallback, $options);
        }

        $this->configuredOptions = $options;

        return $options;
    }

    protected function record(): ?Result
    {
        $attributes = (new \ReflectionClass($this))->getAttributes(Resource::class);

        if (count($attributes) === 0) {
            return null;
        }

        /** @var \Agriweather\EzPayInvoice\Attributes\Resource $resource */
        $resource = $attributes[0]->newInstance();

        return $this->factory->record(
            $resource->name, $resource->action, $this->getConfiguredOptions()
        );
    }
}
