<?php

namespace Agriweather\EzpayInvoice\Enums;

enum AlphanumericCodeStatus: int
{
    /** 暫停 */
    case PAUSED = 0;
    /** 啟用 */
    case ENABLED = 1;
    /** 停用 */
    case DISABLED = 2;
}
