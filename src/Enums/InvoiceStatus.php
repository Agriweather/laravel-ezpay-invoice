<?php

namespace Agriweather\EzpayInvoice\Enums;

enum InvoiceStatus: int
{
    /** 即時開立 */
    case IMMEDIATE = 1;
    /** 延遲開立 */
    case DEFERRED = 0;
    /** 預約自動開立 */
    case SCHEDULED = 3;
}
