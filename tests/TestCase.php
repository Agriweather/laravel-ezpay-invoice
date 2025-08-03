<?php

namespace Agriweather\EzpayInvoice\Tests;

use Agriweather\EzpayInvoice\EzpayInvoiceServiceProvider;
use Carbon\Carbon;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2025-01-01 00:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

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
        $app['config']->set('ezpay_invoice.merchant_hash_key', 'EXAMPLEHASHKEY000000000123456789');
        $app['config']->set('ezpay_invoice.merchant_hash_iv', 'EXAMPLEHASHIV123');
        $app['config']->set('ezpay_invoice.company_id', '111330034');
        $app['config']->set('ezpay_invoice.company_hash_key', 'EXAMPLEHASHKEY110000000123456789');
        $app['config']->set('ezpay_invoice.company_hash_iv', 'EXAMPLEHASHIV456');
        $app['config']->set('ezpay_invoice.env', 'test');
        $app['config']->set('ezpay_invoice.timeout', 30);
    }
}
