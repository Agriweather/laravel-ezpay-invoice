<?php

namespace Agriweather\EzPayInvoice\Enums;

enum AllowanceCreateStatus: int
{
    /** 即時開立 */
    case IMMEDIATE = 1;
    /** 延遲開立 */
    case DEFERRED = 0;
}
