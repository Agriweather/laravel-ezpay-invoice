<?php

namespace Agriweather\EzPayInvoice\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Agriweather\EzPayInvoice\Invoice invoice()
 * @method static \Agriweather\EzPayInvoice\Allowance allowance()
 * @method static \Agriweather\EzPayInvoice\CrossBorder crossBorder()
 * @method static \Agriweather\EzPayInvoice\AlphanumericCode alphanumericCode()
 * @method static \Agriweather\EzPayInvoice\Builders\CodeValidation\CodeValidationBuilder codeValidation()
 *
 * @see \Agriweather\EzPayInvoice\Factory
 */
class EzPayInvoice extends Facade
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
