<?php

namespace Agriweather\EzPayInvoice\Enums\Invoice;

enum InvoiceStatus: int
{
    /** 已開立 (有產生發票號碼) */
    case ISSUED = 1;
    /** 已作廢 */
    case VOIDED = 2;
}
