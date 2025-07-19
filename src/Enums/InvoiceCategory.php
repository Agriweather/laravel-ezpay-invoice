<?php

namespace Agriweather\EzpayInvoice\Enums;

/**
 * 發票種類
 */
enum InvoiceCategory: string
{
    /** 企業對企業 */
    case B2B = 'B2B';
    /** 企業對消費者 */
    case B2C = 'B2C';
}
