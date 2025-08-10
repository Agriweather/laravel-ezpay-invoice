<?php

namespace Agriweather\EzPayInvoice\Enums\Allowance;

enum TriggerStatus: string
{
    /** 確認折讓 */
    case YES = 'C';
    /** 取消折讓 */
    case NO = 'D';
}
