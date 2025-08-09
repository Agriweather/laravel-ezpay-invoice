<?php

namespace Agriweather\EzPayInvoice\Enums;

enum TaxType: int
{
    /** 應稅 */
    case TAXABLE = 1;
    /** 零稅率 */
    case ZERO_RATE = 2;
    /** 免稅 */
    case TAX_FREE = 3;
    /** 混合應稅與免稅或零稅率 */
    case MIXED = 9;
}
