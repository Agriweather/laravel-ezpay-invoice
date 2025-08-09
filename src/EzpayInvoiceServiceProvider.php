<?php

namespace Agriweather\EzPayInvoice;

use Agriweather\EzPayInvoice\Contracts\FormPostSender as FormPostSenderContract;
use Agriweather\EzPayInvoice\Contracts\HttpSender as HttpSenderContract;
use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Senders\FormPostSender;
use Agriweather\EzPayInvoice\Senders\HttpSender;
use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Support\ServiceProvider;

class EzPayInvoiceServiceProvider extends ServiceProvider
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

        $this->app->singleton(Crypto::class, function () {
            return new Crypto;
        });

        $this->app->singleton(HttpSenderContract::class, function ($app) {
            return new HttpSender(
                $app->make(HttpClient::class),
            );
        });

        $this->app->singleton(FormPostSenderContract::class, function () {
            return new FormPostSender;
        });

        $this->app->singleton(Factory::class, function ($app) {
            return new Factory(
                $app->make(Crypto::class),
                $app->make(HttpSenderContract::class),
                $app->make(FormPostSenderContract::class),
                $app['config']->get('ezpay_invoice')
            );
        });

        $this->app->alias(Factory::class, 'ezpay-invoice');
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
