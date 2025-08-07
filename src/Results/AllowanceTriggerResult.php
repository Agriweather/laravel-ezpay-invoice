<?php

namespace Agriweather\EzpayInvoice\Results;

final class AllowanceTriggerResult extends Result
{
    use Concerns\HasAllowance;
    use Concerns\HasCheckCode;
    use Concerns\HasInvoiceNumber;
    use Concerns\HasMerchantID;
    use Concerns\HasOrderNo;
}
