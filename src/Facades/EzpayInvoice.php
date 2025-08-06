<?php

namespace Agriweather\EzpayInvoice\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Agriweather\EzpayInvoice\Invoice invoice()
 * @method static \Agriweather\EzpayInvoice\CodeValidation codeValidation()
 *
 * @see \Agriweather\EzpayInvoice\Factory
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
        return 'ezpay-invoice';
    }
}
