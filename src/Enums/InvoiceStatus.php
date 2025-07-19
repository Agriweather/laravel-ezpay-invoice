<?php

namespace Agriweather\EzpayInvoice\Enums;

/**
 * 開立發票方式
 */
enum InvoiceStatus: int
{
    /** 即時開立 */
    case IMMEDIATE = 1;
    /** 等待觸發 */
    case WAITING = 0;
    /** 預約自動開立 */
    case SCHEDULED = 3;
}
