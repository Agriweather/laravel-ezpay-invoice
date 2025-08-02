<?php

namespace Agriweather\EzpayInvoice\Enums;

enum InvoiceCategory: string
{
    /** 買受人為營業人 */
    case B2B = 'B2B';
    /** 買受人為個人 */
    case B2C = 'B2C';
}
