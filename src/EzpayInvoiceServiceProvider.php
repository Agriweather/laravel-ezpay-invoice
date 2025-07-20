<?php

namespace Agriweather\EzpayInvoice;

use Agriweather\EzpayInvoice\Crypto\EzpayCrypto;
use Illuminate\Support\ServiceProvider;

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
            return new EzpayCrypto(
                $app['config']['ezpay_invoice']['hash_key'],
                $app['config']['ezpay_invoice']['hash_iv']
            );
        });

        $this->app->singleton(EzpayInvoice::class, function ($app) {
            return new EzpayInvoice(
                $app->make(EzpayCrypto::class),
                $app->make('http.client')
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
