<?php

namespace Agriweather\EzPayInvoice\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Agriweather\EzPayInvoice\Resources\Invoice invoice()
 * @method static \Agriweather\EzPayInvoice\Resources\Allowance allowance()
 * @method static \Agriweather\EzPayInvoice\Resources\CrossBorder crossBorder()
 * @method static \Agriweather\EzPayInvoice\Resources\AlphanumericCode alphanumericCode()
 * @method static \Agriweather\EzPayInvoice\Resources\CodeValidation codeValidation()
 * @method static string baseUrl()
 * @method static mixed config(?string $key = null)
 * @method static void fake(\Agriweather\EzPayInvoice\Results\Result[] $results)
 * @method static bool recording()
 * @method static \Agriweather\EzPayInvoice\Results\Result|null record(string $resource, ?string $action, \Agriweather\EzPayInvoice\Options\Options $options)
 * @method static void assertSent(string $resource, string|callable|null $action, ?callable $callback = null)
 * @method static void assertNotSent(string $resource, string|callable|null $action, ?callable $callback = null)
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
