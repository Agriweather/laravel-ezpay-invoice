<?php

namespace Agriweather\EzPayInvoice\Builders\AlphanumericCode;

use Agriweather\EzPayInvoice\Attributes\Resource;
use Agriweather\EzPayInvoice\Builders\Builder;
use Agriweather\EzPayInvoice\Enums\Invoice\InvoiceTerm;
use Agriweather\EzPayInvoice\Enums\Invoice\InvoiceType;
use Agriweather\EzPayInvoice\Options\AlphanumericCode\CreateOptions;
use Agriweather\EzPayInvoice\Resources\AlphanumericCode;
use Agriweather\EzPayInvoice\Results\AlphanumericCode\CreateResult;

#[Resource(AlphanumericCode::class, 'create')]
final class CreateBuilder extends Builder
{
    private CreateOptions $options;

    protected function boot(): void
    {
        $this->crypto->setHashKey($this->factory->config('company_hash_key'));
        $this->crypto->setHashIv($this->factory->config('company_hash_iv'));

        $this->options = new CreateOptions;
        $this->options->companyId = $this->factory->config('company_id');

        $this->endpoint = '/Api_number_management/createNumber';
    }

    protected function options(): CreateOptions
    {
        return $this->options;
    }

    /**
     * 發票年度
     *
     * @param  int  $year  民國年，例如 106。只可輸入今年與明年。
     */
    public function withYear(int $year): self
    {
        $this->options->year = $year;

        return $this;
    }

    /**
     * 發票期別
     */
    public function withTerm(InvoiceTerm $term): self
    {
        $this->options->term = $term;

        return $this;
    }

    /**
     * 字軌英文代碼
     *
     * 兩碼大寫英文
     */
    public function withCode(string $alphanumericCode): self
    {
        $this->options->alphanumericCode = $alphanumericCode;

        return $this;
    }

    /**
     * 發票號碼範圍
     *
     * @param  string  $startNumber  起始號碼。例如：00000001
     * @param  string  $endNumber  結束號碼。例如：00009999
     */
    public function withRange(string $startNumber, string $endNumber): self
    {
        $this->options->startNumber = $startNumber;
        $this->options->endNumber = $endNumber;

        return $this;
    }

    /**
     * 發票類別
     *
     * 為該組字軌的發票類別
     */
    public function withType(InvoiceType $invoiceType): self
    {
        $this->options->type = $invoiceType;

        return $this;
    }

    /**
     * 新增字軌
     *
     * @throws \RuntimeException
     * @throws \Agriweather\EzPayInvoice\Exceptions\EzPayInvoiceException
     */
    public function save(): CreateResult
    {
        if ($result = $this->record()) {
            /** @phpstan-ignore-next-line */
            return $result;
        }

        return new CreateResult($this->sendRequest());
    }
}
