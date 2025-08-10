<?php

namespace Agriweather\EzPayInvoice\Enums\Invoice;

enum InvoiceTerm: int
{
    /** 一、二月 */
    case JAN_FEB = 1;
    /** 三、四月 */
    case MAR_APR = 2;
    /** 五、六月 */
    case MAY_JUN = 3;
    /** 七、八月 */
    case JUL_AUG = 4;
    /** 九、十月 */
    case SEP_OCT = 5;
    /** 十一、十二月 */
    case NOV_DEC = 6;
}
