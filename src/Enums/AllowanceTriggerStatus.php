<?php

namespace Agriweather\EzpayInvoice\Enums;

enum AllowanceTriggerStatus: string
{
    /** 確認折讓 */
    case YES = 'C';
    /** 取消折讓 */
    case NO = 'D';
}
