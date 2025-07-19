<?php

namespace Agriweather\EzpayInvoice\Enums;

/**
 * 載具類別
 */
enum CarrierType: int
{
    /** 手機條碼 */
    case MOBILE = 0;
    /** 自然人憑證 */
    case CITIZEN_CARD = 1;
    /** ezPay電子發票載具 */
    case EZPAY_CARRIER = 2;
}
