<?php

namespace Agriweather\EzPayInvoice;

use Agriweather\EzPayInvoice\Contracts\FormRedirectTransporter;
use Agriweather\EzPayInvoice\Contracts\HttpTransporter;
use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Options\Options;
use Agriweather\EzPayInvoice\Resources\Allowance;
use Agriweather\EzPayInvoice\Resources\AlphanumericCode;
use Agriweather\EzPayInvoice\Resources\CodeValidation;
use Agriweather\EzPayInvoice\Resources\CrossBorder;
use Agriweather\EzPayInvoice\Resources\Invoice;
use Agriweather\EzPayInvoice\Results\Result;
use Agriweather\EzPayInvoice\Testing\TestRequest;
use PHPUnit\Framework\Assert as PHPUnit;
use RuntimeException;

class Factory
{
    /**
     * ezPay 發票平台生產環境的 baseURL。
     */
    protected string $productionBaseUrl = 'https://inv.ezpay.com.tw';

    /**
     * ezPay 發票平台測試環境的 baseURL。
     */
    protected string $testingBaseUrl = 'https://cinv.ezpay.com.tw';

    /**
     * 是否開啟紀錄假資料模式
     */
    protected bool $recording = false;

    /**
     * 已紀錄的請求選項
     *
     * @var \Agriweather\EzPayInvoice\Testing\TestRequest[]
     */
    protected array $requests = [];

    /**
     * 已設定的假回傳資料
     *
     * @var \Agriweather\EzPayInvoice\Results\Result[]
     */
    protected array $results = [];

    public function __construct(
        protected Crypto $crypto,
        protected HttpTransporter $httpTransporter,
        protected FormRedirectTransporter $formRedirectTransporter,
        protected array $config
    ) {
        //
    }

    public function invoice(): Invoice
    {
        return new Invoice(
            $this, $this->crypto, $this->httpTransporter, $this->formRedirectTransporter
        );
    }

    public function allowance(): Allowance
    {
        return new Allowance(
            $this, $this->crypto, $this->httpTransporter
        );
    }

    public function crossBorder(): CrossBorder
    {
        return new CrossBorder(
            $this, $this->crypto, $this->httpTransporter, $this->formRedirectTransporter
        );
    }

    public function alphanumericCode(): AlphanumericCode
    {
        return new AlphanumericCode(
            $this, $this->crypto, $this->httpTransporter
        );
    }

    public function codeValidation(): CodeValidation
    {
        return new CodeValidation(
            $this, $this->crypto, $this->httpTransporter
        );
    }

    public function baseUrl(): string
    {
        return $this->config['env'] === 'production'
            ? $this->productionBaseUrl
            : $this->testingBaseUrl;
    }

    public function config(?string $key = null)
    {
        if (isset($key)) {
            return $this->config[$key] ?? null;
        }

        return $this->config;
    }

    /**
     * 設定假回傳資料
     *
     * @param  \Agriweather\EzPayInvoice\Results\Result[]  $results
     */
    public function fake(array $results): void
    {
        $this->recording = true;

        $this->results = $results;
    }

    /**
     * 目前是否開啟紀錄假資料模式
     */
    public function recording(): bool
    {
        return $this->recording;
    }

    /**
     * 紀錄請求選項，並回傳模擬資料
     *
     * @throws \RuntimeException
     */
    public function record(string $resource, ?string $action, Options $options): ?Result
    {
        if (! $this->recording) {
            return null;
        }

        $this->requests[] = new TestRequest(
            $resource, $action, $options
        );

        /** @var \Agriweather\EzPayInvoice\Results\Result|null $result */
        $result = array_shift($this->results);

        if (is_null($result)) {
            throw new RuntimeException('No more fake results available.');
        }

        return $result;
    }

    /**
     * 斷言已經送出指定的請求
     *
     * @throws \PHPUnit\Framework\ExpectationFailedException
     */
    public function assertSent(string $resource, string|callable|null $action, ?callable $callback = null): void
    {
        $resourceName = "[{$resource}".(is_string($action) ? "::{$action}" : '').']';

        PHPUnit::assertTrue(
            $this->sent($resource, $action, $callback) !== [],
            "The expected {$resourceName} request was not sent."
        );
    }

    /**
     * 斷言沒有送出指定的請求
     *
     * @throws \PHPUnit\Framework\ExpectationFailedException
     */
    public function assertNotSent(string $resource, string|callable|null $action, ?callable $callback = null): void
    {
        $resourceName = "[{$resource}".(is_string($action) ? "::{$action}" : '').']';

        PHPUnit::assertTrue(
            $this->sent($resource, $action, $callback) === [],
            "The unexpected {$resourceName} request was sent."
        );
    }

    protected function sent(string $resource, string|callable|null $action, ?callable $callback): array
    {
        if (is_callable($action) && is_null($callback)) {
            $callback = $action;
            $action = null;
        }

        $requestOptions = $this->resourcesOf($resource, $action);

        if ($requestOptions === []) {
            return [];
        }

        $callback = $callback ?: fn (): bool => true;

        return array_filter($requestOptions, function (TestRequest $request) use ($callback): bool {
            return $callback($request->options());
        });
    }

    /**
     * @return \Agriweather\EzPayInvoice\Testing\TestRequest[]
     */
    protected function resourcesOf(string $resource, ?string $action): array
    {
        return array_filter($this->requests, function (TestRequest $request) use ($resource, $action): bool {
            return $request->resource() === $resource
                && (is_null($action) || $request->action() === $action);
        });
    }
}
