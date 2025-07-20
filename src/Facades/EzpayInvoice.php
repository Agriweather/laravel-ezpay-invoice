<?php

namespace Agriweather\EzpayInvoice\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * ezPay 電子發票
 */
class EzpayInvoice extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Agriweather\EzpayInvoice\EzpayInvoice::class;
    }
}
