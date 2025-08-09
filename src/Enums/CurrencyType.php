<?php

namespace Agriweather\EzPayInvoice\Enums;

enum CurrencyType: string
{
    /** 美元 */
    case USD = 'USD';
    /** 港幣 */
    case HKD = 'HKD';
    /** 英鎊 */
    case GBP = 'GBP';
    /** 澳幣 */
    case AUD = 'AUD';
    /** 加幣 */
    case CAD = 'CAD';
    /** 新加坡幣 */
    case SGD = 'SGD';
    /** 瑞士法郎 */
    case CHF = 'CHF';
    /** 日圓 */
    case JPY = 'JPY';
    /** 南非蘭特 */
    case ZAR = 'ZAR';
    /** 瑞典克朗 */
    case SEK = 'SEK';
    /** 紐西蘭幣 */
    case NZD = 'NZD';
    /** 泰銖 */
    case THB = 'THB';
    /** 菲律賓披索 */
    case PHP = 'PHP';
    /** 印尼盧比 */
    case IDR = 'IDR';
    /** 歐元 */
    case EUR = 'EUR';
    /** 韓圓 */
    case KRW = 'KRW';
    /** 越南盾 */
    case VND = 'VND';
    /** 馬來西亞令吉 */
    case MYR = 'MYR';
    /** 人民幣 */
    case CNY = 'CNY';
    /** 新台幣 */
    case TWD = 'TWD';
}
