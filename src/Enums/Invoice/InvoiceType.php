<?php

namespace Agriweather\EzPayInvoice\Enums\Invoice;

enum InvoiceType: string
{
    /** 一般稅額計算 */
    case GENERAL = '07';
    /** 特種稅額計算 */
    case SPECIAL = '08';
}
