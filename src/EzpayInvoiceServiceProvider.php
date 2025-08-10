<?php

namespace Agriweather\EzPayInvoice;

use Agriweather\EzPayInvoice\Contracts\FormRedirectTransporter as FormRedirectTransporterContract;
use Agriweather\EzPayInvoice\Contracts\HttpTransporter as HttpTransporterContract;
use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Transporters\FormRedirectTransporter;
use Agriweather\EzPayInvoice\Transporters\HttpTransporter;
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
        $this->mergeConfigFrom(__DIR__.'/../config/ezpay_invoice.php', 'ezpay_invoice');

        $this->app->singleton(Crypto::class, function () {
            return new Crypto;
        });

        $this->app->singleton(HttpTransporterContract::class, function ($app) {
            return new HttpTransporter($app->make(HttpClient::class));
        });

        $this->app->singleton(FormRedirectTransporterContract::class, function () {
            return new FormRedirectTransporter;
        });

        $this->app->singleton(Factory::class, function ($app) {
            return new Factory(
                $app->make(Crypto::class),
                $app->make(HttpTransporterContract::class),
                $app->make(FormRedirectTransporterContract::class),
                $app->make('config')->get('ezpay_invoice')
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
