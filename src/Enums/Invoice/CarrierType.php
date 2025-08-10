<?php

namespace Agriweather\EzPayInvoice\Enums\Invoice;

enum CarrierType: int
{
    /** 手機條碼 */
    case MOBILE = 0;
    /** 自然人憑證 */
    case CITIZEN_CERT = 1;
    /** ezPay電子發票載具 */
    case EZPAY_CARRIER = 2;
}
