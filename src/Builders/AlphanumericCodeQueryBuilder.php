<?php

namespace Agriweather\EzpayInvoice\Builders;

use Agriweather\EzpayInvoice\Enums\AlphanumericCodeStatus;
use Agriweather\EzpayInvoice\Enums\InvoiceTerm;
use Agriweather\EzpayInvoice\Options\AlphanumericCodeQueryOptions;
use Agriweather\EzpayInvoice\Results\AlphanumericCodeQueryResult;
use Agriweather\EzpayInvoice\Results\AlphanumericCodeUpdateResult;

class AlphanumericCodeQueryBuilder extends Builder
{
    protected AlphanumericCodeQueryOptions $options;

    protected function boot(): void
    {
        $this->crypto->setHashKey($this->factory->config('company_hash_key'));
        $this->crypto->setHashIv($this->factory->config('company_hash_iv'));

        $this->options = new AlphanumericCodeQueryOptions;
        $this->options->companyId = $this->factory->config('company_id');
    }

    public function getOptions(): AlphanumericCodeQueryOptions
    {
        return $this->options;
    }

    /**
     * 字軌管理編號
     *
     * 該組字軌於 ezPay 電子發票加值服務平台的流水編號。
     */
    public function withNo(string $managementNo): self
    {
        $this->options->managementNo = $managementNo;

        return $this;
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
     *
     * 為該組字軌的發票期別
     */
    public function withTerm(InvoiceTerm $term): self
    {
        $this->options->term = $term;

        return $this;
    }

    /**
     * 字軌狀態
     *
     * - 暫停 (`AlphanumericCodeFlag::PAUSED`): 此狀態為該組字軌待用中，當啟用中字軌張數用畢，將由系統依同期別字軌建立時間次序切換下組暫停中字軌。
     * - 啟用 (`AlphanumericCodeFlag::ACTIVE`): 此狀態為該組字軌使用中，目前會員發票開立使用該組字軌，會員僅能啟用一組未過期別之字軌，當會員首次新增字軌時，系統將自動啟用該組字軌。
     * - 停用 (`AlphanumericCodeFlag::DISABLED`): 此狀態為停用該組字軌，無法再次啟用。
     */
    public function withStatus(AlphanumericCodeStatus $status): self
    {
        $this->options->status = $status;

        return $this;
    }

    /**
     * 暫停字軌
     *
     * 此狀態為該組字軌待用中，當啟用中字軌張數用畢，將由系統依同期別字軌建立時間次序切換下組暫停中字軌。
     */
    public function withPaused(): self
    {
        $this->withStatus(AlphanumericCodeStatus::PAUSED);

        return $this;
    }

    /**
     * 啟用字軌
     *
     * 此狀態為該組字軌使用中，目前會員發票開立使用該組字軌，會員僅能啟用一組未過期別之字軌，當會員首次新增字軌時，系統將自動啟用該組字軌。
     */
    public function withEnabled(): self
    {
        $this->withStatus(AlphanumericCodeStatus::ENABLED);

        return $this;
    }

    /**
     * 停用字軌
     *
     * 此狀態為停用該組字軌，無法再次啟用。
     */
    public function withDisabled(): self
    {
        $this->withStatus(AlphanumericCodeStatus::DISABLED);

        return $this;
    }

    /**
     * 查詢字軌
     *
     * @return AlphanumericCodeQueryResult[]
     *
     * @throws \Agriweather\EzpayInvoice\Exceptions\EzpayInvoiceException
     */
    public function get(): array
    {
        $this->endpoint = '/Api_number_management/searchNumber';

        $data = $this->sendRequest();

        return array_map(function ($result) {
            return new AlphanumericCodeQueryResult([
                'Result' => $result,
            ]);
        }, $data['Result']);
    }

    /**
     * 暫停字軌
     *
     * @throws \Agriweather\EzpayInvoice\Exceptions\EzpayInvoiceException
     */
    public function pause(): AlphanumericCodeUpdateResult
    {
        $this->endpoint = '/Api_number_management/manageNumber';

        $this->withPaused();

        return new AlphanumericCodeUpdateResult($this->sendRequest());
    }

    /**
     * 啟用字軌
     *
     * @throws \Agriweather\EzpayInvoice\Exceptions\EzpayInvoiceException
     */
    public function enable(): AlphanumericCodeUpdateResult
    {
        $this->endpoint = '/Api_number_management/manageNumber';

        $this->withEnabled();

        return new AlphanumericCodeUpdateResult($this->sendRequest());
    }

    /**
     * 停用字軌
     *
     * @throws \Agriweather\EzpayInvoice\Exceptions\EzpayInvoiceException
     */
    public function disable(): AlphanumericCodeUpdateResult
    {
        $this->endpoint = '/Api_number_management/manageNumber';

        $this->withDisabled();

        return new AlphanumericCodeUpdateResult($this->sendRequest());
    }
}
