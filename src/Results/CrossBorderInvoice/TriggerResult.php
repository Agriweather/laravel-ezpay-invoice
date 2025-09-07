<?php

namespace Agriweather\EzPayInvoice\Results\CrossBorderInvoice;

use Agriweather\EzPayInvoice\Contracts\CheckCodeVerifiable;
use Agriweather\EzPayInvoice\Results\Concerns;
use Agriweather\EzPayInvoice\Results\Result;

final class TriggerResult extends Result implements CheckCodeVerifiable
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
