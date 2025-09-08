<?php

namespace Agriweather\EzPayInvoice\Results\Allowance;

use Agriweather\EzPayInvoice\Results\Concerns;
use Agriweather\EzPayInvoice\Results\Result;

final class CreateResult extends Result
{
    use Concerns\HasCheckCode;
    use Concerns\HasInvoiceNumber;
    use Concerns\HasMerchantID;
    use Concerns\HasOrderNo;

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
