<?php

namespace Agriweather\EzpayInvoice\Results;

use Agriweather\EzpayInvoice\Contracts\CheckCodeVerifiable;

final class CrossBorderInvoiceCreateResult extends Result implements CheckCodeVerifiable
{
    use Concerns\HasCheckCode;
    use Concerns\HasCreateTime;
    use Concerns\HasInvoiceNumber;
    use Concerns\HasInvoiceTransNo;
    use Concerns\HasMerchantID;
    use Concerns\HasOrderNo;
    use Concerns\HasRandomNumber;

    /**
     * 發票金額
     */
    public function totalAmount(): float
    {
        return (float) $this->result['TotalAmt'];
    }
}
