<?php

namespace Agriweather\EzPayInvoice\Builders\CrossBorderAllowance;

use Agriweather\EzPayInvoice\Attributes\Resource;
use Agriweather\EzPayInvoice\Builders\Builder;
use Agriweather\EzPayInvoice\Options\CrossBorderAllowance\InvalidateOptions;
use Agriweather\EzPayInvoice\Resources\CrossBorderAllowance;
use Agriweather\EzPayInvoice\Results\CrossBorderAllowance\InvalidateResult;

#[Resource(CrossBorderAllowance::class, 'voidable')]
final class InvalidateBuilder extends Builder
{
    private InvalidateOptions $options;

    protected function boot(): void
    {
        $this->crypto->setHashKey($this->factory->config('merchant_hash_key'));
        $this->crypto->setHashIv($this->factory->config('merchant_hash_iv'));

        $this->options = new InvalidateOptions;
        $this->options->merchantId = $this->factory->config('merchant_id');

        $this->endpoint = '/Api/allowanceInvalid';
    }

    protected function options(): InvalidateOptions
    {
        return $this->options;
    }

    /**
     * 折讓號
     *
     * @param  string  $allowanceNo  開立折讓時的折讓號
     */
    public function withAllowance(string $allowanceNo): self
    {
        $this->options->allowanceNo = $allowanceNo;

        return $this;
    }

    /**
     * 作廢原因
     *
     * @param  string  $invalidReason  作廢原因，字數限中文 6 字或英文 20 字。
     */
    public function because(string $invalidReason): self
    {
        $this->options->invalidReason = $invalidReason;

        return $this;
    }

    /**
     * 作廢折讓
     *
     * @throws \RuntimeException
     * @throws \Agriweather\EzPayInvoice\Exceptions\EzPayInvoiceException
     */
    public function invalidate(): InvalidateResult
    {
        if ($result = $this->record()) {
            /** @phpstan-ignore-next-line */
            return $result;
        }

        return new InvalidateResult($this->sendRequest());
    }
}
