<?php

namespace Agriweather\EzPayInvoice\Results;

final class InvoiceInvalidateResult extends Result
{
    use Concerns\HasCheckCode;
    use Concerns\HasCreateTime;
    use Concerns\HasInvoiceNumber;
    use Concerns\HasMerchantID;
}
