<?php

namespace Agriweather\EzPayInvoice\Enums;

enum ItemTaxType: int
{
    /** 應稅 */
    case TAXABLE = 1;
    /** 零稅率 */
    case ZERO_RATE = 2;
    /** 免稅 */
    case TAX_FREE = 3;
}
