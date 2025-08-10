<?php

namespace Agriweather\EzPayInvoice\Enums\Allowance;

enum CreateStatus: int
{
    /** 即時開立 */
    case IMMEDIATE = 1;
    /** 延遲開立 */
    case DEFERRED = 0;
}
