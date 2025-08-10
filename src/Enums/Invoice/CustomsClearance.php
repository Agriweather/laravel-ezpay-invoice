<?php

namespace Agriweather\EzPayInvoice\Enums\Invoice;

enum CustomsClearance: int
{
    /** 非經海關 */
    case NON_CUSTOMS = 1;
    /** 經海關 */
    case CUSTOMS = 2;
}
