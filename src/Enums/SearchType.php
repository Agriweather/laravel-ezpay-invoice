<?php

namespace Agriweather\EzpayInvoice\Enums;

enum SearchType: int
{
    /** 發票號碼 + 隨機碼 */
    case BY_INVOICE_NUMBER = 0;
    /** 訂單編號 + 發票金額 */
    case BY_ORDER_NUMBER = 1;
}
