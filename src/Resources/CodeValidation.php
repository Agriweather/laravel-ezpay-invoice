<?php

namespace Agriweather\EzPayInvoice\Resources;

use Agriweather\EzPayInvoice\Builders\CodeValidation\CodeValidationBuilder;
use Agriweather\EzPayInvoice\Contracts\HttpTransporter;
use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Factory;

/**
 * @mixin \Agriweather\EzPayInvoice\Builders\CodeValidation\CodeValidationBuilder
 */
final class CodeValidation
{
    use Concerns\PrepareBuilder;

    public function __construct(
        private readonly Factory $factory,
        private readonly Crypto $crypto,
        private readonly HttpTransporter $httpTransporter
    ) {
        //
    }

    public function codeValidation(): CodeValidationBuilder
    {
        return $this->prepareBuilder(new CodeValidationBuilder(
            $this->factory, $this->crypto, $this->httpTransporter
        ));
    }

    public function __call(string $method, array $parameters)
    {
        return $this->codeValidation()->$method(...$parameters);
    }
}
