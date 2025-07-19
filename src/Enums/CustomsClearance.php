<?php

namespace Agriweather\EzpayInvoice\Enums;

/**
 * 報關標記
 */
enum CustomsClearance: int
{
    /** 非經海關 */
    case NON_CUSTOMS = 1;
    /** 經海關 */
    case CUSTOMS = 2;
}
