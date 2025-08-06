<?php

namespace Agriweather\EzpayInvoice;

use Agriweather\EzpayInvoice\Contracts\FormPostSender as FormPostSenderContract;
use Agriweather\EzpayInvoice\Contracts\HttpSender as HttpSenderContract;
use Agriweather\EzpayInvoice\Crypto\EzpayCrypto;
use Agriweather\EzpayInvoice\Senders\FormPostSender;
use Agriweather\EzpayInvoice\Senders\HttpSender;
use Illuminate\Http\Client\Factory as HttpClient;
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

        $this->app->singleton(EzpayCrypto::class, function () {
            return new EzpayCrypto;
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
                $app->make(EzpayCrypto::class),
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
