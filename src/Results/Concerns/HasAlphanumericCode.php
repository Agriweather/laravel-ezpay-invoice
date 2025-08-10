<?php

namespace Agriweather\EzPayInvoice\Results\Concerns;

use Agriweather\EzPayInvoice\Enums\AlphanumericCode\AlphanumericCodeStatus;
use Agriweather\EzPayInvoice\Enums\Invoice\InvoiceTerm;
use Agriweather\EzPayInvoice\Enums\Invoice\InvoiceType;
use Carbon\Carbon;

trait HasAlphanumericCode
{
    /**
     * 字軌管理編號
     */
    public function managementNo(): string
    {
        return $this->result['ManagementNo'];
    }

    /**
     * 發票年度
     */
    public function year(): int
    {
        return (int) $this->result['Year'];
    }

    /**
     * 發票期別
     *
     * 為該組字軌的發票期別
     *
     * @throws \ValueError
     */
    public function term(): InvoiceTerm
    {
        return InvoiceTerm::from((int) $this->result['Term']);
    }

    /**
     * 字軌英文代碼
     *
     * 兩碼大寫英文
     */
    public function alphabeticLetter(): string
    {
        return $this->result['AphabeticLetter'];
    }

    /**
     * 發票起始號碼
     */
    public function startNumber(): string
    {
        return $this->result['StartNumber'];
    }

    /**
     * 發票結束號碼
     */
    public function endNumber(): string
    {
        return $this->result['EndNumber'];
    }

    /**
     * 發票類別
     *
     * @throws \ValueError
     */
    public function type(): InvoiceType
    {
        return InvoiceType::from($this->result['Type']);
    }

    /**
     * 字軌建立日期
     *
     * @throws \Carbon\Exceptions\InvalidFormatException
     */
    public function createDatetime(): Carbon
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->result['CreateDatetime']);
    }

    /**
     * 該組字軌剩餘張數
     */
    public function lastNumber(): int
    {
        return (int) $this->result['LastNumber'];
    }

    /**
     * 字軌狀態
     *
     * @throws \ValueError
     */
    public function status(): AlphanumericCodeStatus
    {
        return AlphanumericCodeStatus::from((int) $this->result['Flag']);
    }
}
