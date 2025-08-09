<?php

namespace Agriweather\EzPayInvoice\Enums;

enum InvoicePrintFlag: string
{
    /** 索取紙本發票 */
    case YES = 'Y';
    /** 不索取紙本發票 */
    case NO = 'N';
}
