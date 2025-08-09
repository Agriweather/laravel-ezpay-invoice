<?php

namespace Agriweather\EzPayInvoice\Results;

use Agriweather\EzPayInvoice\Contracts\CheckCodeVerifiable;

final class InvoiceTriggerResult extends Result implements CheckCodeVerifiable
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
    public function totalAmount(): int
    {
        return (int) $this->result['TotalAmt'];
    }
}
