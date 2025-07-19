<?php

namespace Agriweather\EzpayInvoice;

use Agriweather\EzpayInvoice\Crypto\EzpayCrypto;
use Illuminate\Support\ServiceProvider;

class EzpayInvoiceServiceProvider extends ServiceProvider
{
    /**
     * 註冊套件服務
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/ezpay_invoice.php', 'ezpay_invoice'
        );

        $this->app->singleton(EzpayCrypto::class);
        $this->app->singleton(EzpayInvoice::class);
    }

    /**
     * 啟動套件服務
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
