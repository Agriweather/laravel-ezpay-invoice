<?php

namespace Agriweather\EzPayInvoice\Results\Invoice;

use Agriweather\EzPayInvoice\Contracts\CheckCodeVerifiable;
use Agriweather\EzPayInvoice\Results\Concerns;
use Agriweather\EzPayInvoice\Results\Result;

final class CreateResult extends Result implements CheckCodeVerifiable
{
    use Concerns\HasBarCode;
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
