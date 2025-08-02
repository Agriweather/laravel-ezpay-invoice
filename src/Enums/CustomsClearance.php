<?php

namespace Agriweather\EzpayInvoice\Enums;

enum CustomsClearance: int
{
    /** 非經海關 */
    case NON_CUSTOMS = 1;
    /** 經海關 */
    case CUSTOMS = 2;
}
