<?php

namespace Agriweather\EzPayInvoice\Enums\Allowance;

enum CreateStatus: int
{
    /** 開立折讓後，立即確認折讓 */
    case IMMEDIATE = 1;
    /** 開立折讓後，不立即確認折讓，需等待買受人確認折讓 */
    case PENDING = 0;
}
