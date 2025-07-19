<?php

namespace Agriweather\EzpayInvoice\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * ezPay 電子發票 Facade
 */
class EzpayInvoice extends Facade
{
    /**
     * 取得 Facade 對應的服務名稱
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Agriweather\EzpayInvoice\EzpayInvoice::class;
    }
}
