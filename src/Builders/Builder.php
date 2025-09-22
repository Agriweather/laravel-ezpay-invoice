<?php

namespace Agriweather\EzPayInvoice\Builders;

use Agriweather\EzPayInvoice\Attributes\Resource;
use Agriweather\EzPayInvoice\Contracts\FormRedirectTransporter;
use Agriweather\EzPayInvoice\Contracts\HttpTransporter;
use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Exceptions\EzPayInvoiceException;
use Agriweather\EzPayInvoice\Factory;
use Agriweather\EzPayInvoice\Options\Options;
use Agriweather\EzPayInvoice\Results\Result;
use Illuminate\Http\Response;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Tappable;
use ReflectionClass;

abstract class Builder
{
    use Concerns\Dumpable;
    use Concerns\HasPrepareOptions;
    use Conditionable;
    use Tappable;

    /**
     * API 端點
     */
    protected string $endpoint = '';

    /**
     * 已經設定完成、且可準備送出的的選項
     */
    private ?Options $preparedOptions = null;

    protected ?FormRedirectTransporter $formRedirectTransporter = null;

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
     * @throws \RuntimeException
     * @throws \Agriweather\EzPayInvoice\Exceptions\EzPayInvoiceException
     */
    protected function sendRequest(): array
    {
        $requestData = $this->toRequestData();

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
        $options = $this->getPreparedOptions();
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
     * 解析並設定完成可準備送出的的選項
     */
    protected function getPreparedOptions(): Options
    {
        if ($this->preparedOptions) {
            return $this->preparedOptions;
        }

        $options = $this->options();

        if ($this->onPrepareOptionsCallback) {
            call_user_func($this->onPrepareOptionsCallback, $options);
        }

        $this->preparedOptions = $options;

        return $options;
    }

    /**
     * 發送跳轉到 ezPay 平台的請求
     */
    protected function sendFormRedirectRequest(): Response
    {
        $requestData = $this->toRedirectRequestData();

        return $this->formRedirectTransporter->send(
            $requestData['url'],
            $requestData['formData']
        );
    }

    /**
     * 發送跳轉到 ezPay 平台的請求表單資料
     */
    public function toRedirectRequestData(): array
    {
        return $this->toRequestData();
    }

    public function setFormRedirectTransporter(FormRedirectTransporter $formRedirectTransporter): static
    {
        $this->formRedirectTransporter = $formRedirectTransporter;

        return $this;
    }

    /**
     * 紀錄請求選項，並回傳模擬資料
     *
     * @throws \RuntimeException
     */
    protected function record(): ?Result
    {
        $attributes = (new ReflectionClass($this))->getAttributes(Resource::class);

        if (count($attributes) === 0) {
            return null;
        }

        /** @var \Agriweather\EzPayInvoice\Attributes\Resource $resource */
        $resource = $attributes[0]->newInstance();

        return $this->factory->record(
            $resource->name, $resource->action, $this->getPreparedOptions()
        );
    }
}
