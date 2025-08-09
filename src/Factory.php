<?php

namespace Agriweather\EzPayInvoice;

use Agriweather\EzPayInvoice\Builders\CodeValidationBuilder;
use Agriweather\EzPayInvoice\Contracts\FormPostSender;
use Agriweather\EzPayInvoice\Contracts\HttpSender;
use Agriweather\EzPayInvoice\Crypto\Crypto;

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

    public function __construct(
        protected Crypto $crypto,
        protected HttpSender $httpSender,
        protected FormPostSender $formPostSender,
        protected array $config
    ) {
        //
    }

    public function invoice(): Invoice
    {
        return new Invoice(
            $this, $this->crypto, $this->httpSender, $this->formPostSender
        );
    }

    public function allowance(): Allowance
    {
        return new Allowance(
            $this, $this->crypto, $this->httpSender
        );
    }

    public function crossBorder(): CrossBorder
    {
        return new CrossBorder(
            $this, $this->crypto, $this->httpSender, $this->formPostSender
        );
    }

    public function alphanumericCode(): AlphanumericCode
    {
        return new AlphanumericCode(
            $this, $this->crypto, $this->httpSender
        );
    }

    public function codeValidation(): CodeValidationBuilder
    {
        return new CodeValidationBuilder(
            $this, $this->crypto, $this->httpSender
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
}
