<?php

namespace Tests;

use Agriweather\EzpayInvoice\EzpayInvoiceServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            EzpayInvoiceServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        // 設定測試專用的環境變數
        $app['config']->set('ezpay_invoice.merchant_id', '111335678');
        $app['config']->set('ezpay_invoice.merchant_hash_key', 'TEST_MERCHANT_HASH_KEY');
        $app['config']->set('ezpay_invoice.merchant_hash_iv', 'TEST_MERCHANT_HASH_IV');
        $app['config']->set('ezpay_invoice.company_id', '111330034');
        $app['config']->set('ezpay_invoice.company_hash_key', 'TEST_COMPANY_HASH_KEY');
        $app['config']->set('ezpay_invoice.company_hash_iv', 'TEST_COMPANY_HASH_IV');
        $app['config']->set('ezpay_invoice.env', 'test');
        $app['config']->set('ezpay_invoice.timeout', 30);
    }
}
