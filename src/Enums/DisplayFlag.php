<?php

namespace Agriweather\EzPayInvoice\Enums;

enum DisplayFlag: int
{
    /** 於平台網頁顯示 */
    case WEB_DISPLAY = 1;
    /** 回傳網址 */
    case RETURN_URL = 2;
}
