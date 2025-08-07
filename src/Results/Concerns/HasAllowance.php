<?php

namespace Agriweather\EzpayInvoice\Results\Concerns;

trait HasAllowance
{
    /**
     * 折讓號
     */
    public function allowanceNo(): string
    {
        return $this->result['AllowanceNo'];
    }

    /**
     * 折讓金額
     */
    public function allowanceAmount(): int
    {
        return (int) $this->result['AllowanceAmt'];
    }

    /**
     * 折讓後剩餘發票金額
     */
    public function remainingAmount(): int
    {
        return (int) $this->result['RemainAmt'];
    }
}
