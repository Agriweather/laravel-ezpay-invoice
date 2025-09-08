<?php

namespace Agriweather\EzPayInvoice\Results\CrossBorderAllowance;

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
    public function allowanceAmount(): float
    {
        return (float) $this->result['AllowanceAmt'];
    }

    /**
     * 折讓後剩餘發票金額
     */
    public function remainingAmount(): float
    {
        return (float) $this->result['RemainAmt'];
    }
}
