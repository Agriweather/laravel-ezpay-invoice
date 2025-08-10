<?php

namespace Agriweather\EzPayInvoice\Results\Allowance;

use Agriweather\EzPayInvoice\Results\Concerns;
use Agriweather\EzPayInvoice\Results\Result;

final class TriggerResult extends Result
{
    use Concerns\HasAllowance;
    use Concerns\HasCheckCode;
    use Concerns\HasInvoiceNumber;
    use Concerns\HasMerchantID;
    use Concerns\HasOrderNo;
}
