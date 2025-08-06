<?php

namespace Agriweather\EzpayInvoice\Results;

final class InvoiceVoidResult extends Result
{
    use Concerns\HasCheckCode;
    use Concerns\HasCreateTime;
    use Concerns\HasInvoiceNumber;
    use Concerns\HasMerchantID;
}
