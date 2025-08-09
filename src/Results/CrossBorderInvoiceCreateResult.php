<?php

namespace Agriweather\EzPayInvoice\Results;

use Agriweather\EzPayInvoice\Contracts\CheckCodeVerifiable;

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
