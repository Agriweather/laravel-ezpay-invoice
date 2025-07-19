<?php

namespace Agriweather\EzpayInvoice\Enums;

/**
 * 回傳格式
 */
enum RespondType: string
{
    case JSON = 'JSON';
    case STRING = 'String';
}
