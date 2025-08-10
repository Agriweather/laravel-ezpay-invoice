<?php

namespace Agriweather\EzPayInvoice\Results\Invoice;

use Agriweather\EzPayInvoice\Results\Concerns;
use Agriweather\EzPayInvoice\Results\Result;

final class InvalidateResult extends Result
{
    use Concerns\HasCheckCode;
    use Concerns\HasCreateTime;
    use Concerns\HasInvoiceNumber;
    use Concerns\HasMerchantID;
}
