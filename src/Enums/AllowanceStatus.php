<?php

namespace Agriweather\EzpayInvoice\Enums;

/**
 * 確認折讓方式
 */
enum AllowanceStatus: int
{
    /** 不立即確認 */
    case NOT_CONFIRM = 0;
    /** 立即確認 */
    case CONFIRM = 1;
}
