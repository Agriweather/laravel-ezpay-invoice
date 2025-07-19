<?php

namespace Agriweather\EzpayInvoice\Enums;

/**
 * 查詢方式
 */
enum SearchType: int
{
    /** 發票號碼+隨機碼 */
    case BY_INVOICE_NUMBER = 0;
    /** 訂單編號+金額 */
    case BY_ORDER_NUMBER = 1;
}
