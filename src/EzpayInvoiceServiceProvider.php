<?php

namespace Agriweather\EzpayInvoice;

use Agriweather\EzpayInvoice\Crypto\EzpayCrypto;
use Illuminate\Support\ServiceProvider;

// use Agriweather\EzpayInvoice\Factory;

class EzpayInvoiceServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/ezpay_invoice.php', 'ezpay_invoice'
        );

        $this->app->singleton(EzpayCrypto::class, function ($app) {
            return new EzpayCrypto;
        });

        $this->app->singleton(Factory::class, function ($app) {
            return new Factory(
                $app->make(EzpayCrypto::class),
                $app['config']->get('ezpay_invoice'),
            );
        });
    }

    /**
     * Boot the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/ezpay_invoice.php' => config_path('ezpay_invoice.php'),
            ], 'ezpay-invoice-config');
        }
    }
}
