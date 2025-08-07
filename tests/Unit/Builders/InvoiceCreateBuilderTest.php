<?php

use Agriweather\EzpayInvoice\Builders\InvoiceCreateBuilder;
use Agriweather\EzpayInvoice\Contracts\HttpSender;
use Agriweather\EzpayInvoice\Crypto\EzpayCrypto;
use Agriweather\EzpayInvoice\Enums\CarrierType;
use Agriweather\EzpayInvoice\Enums\CustomsClearance;
use Agriweather\EzpayInvoice\Enums\TaxType;
use Agriweather\EzpayInvoice\Factory;
use Carbon\Carbon;
use Illuminate\Http\Client\Response as HttpClientResponse;

use function Pest\Laravel\mock;

describe('InvoiceCreateBuilder', function () {
    beforeEach(function () {
        Carbon::setTestNow('2025-01-01 00:00:00');
    });

    afterEach(function () {
        Carbon::setTestNow();
    });

    test('可以僅使用必須的參數', function () {
        $expectedPostData = [
            'RespondType' => 'JSON',
            'Version' => '1.5',
            'TimeStamp' => Carbon::now()->timestamp,
            'MerchantOrderNo' => 'Order123',
            'Status' => '1',
            'Category' => 'B2C',
            'BuyerName' => 'John Doe',
            'PrintFlag' => 'Y',
            'TaxType' => '1',
            'TaxRate' => '5',
            'Amt' => '200',
            'TaxAmt' => '5',
            'TotalAmt' => '205',
            'ItemName' => '商品名稱',
            'ItemCount' => '2',
            'ItemUnit' => '個',
            'ItemPrice' => '100',
            'ItemAmt' => '200',
        ];

        /** @var \Mockery\LegacyMockInterface&\Mockery\MockInterface&\Illuminate\Http\Client\Response */
        $response = mock(HttpClientResponse::class);
        $response->expects('json')->andReturn(['Status' => 'SUCCESS']);
        /** @var \Mockery\LegacyMockInterface&\Mockery\MockInterface&\Agriweather\EzpayInvoice\Factory */
        $factory = mock(Factory::class);
        $factory->expects('baseUrl')->andReturn('https://example.com/api/');
        $factory->shouldReceive('config')->andReturn('1234567890');
        /** @var \Mockery\LegacyMockInterface&\Mockery\MockInterface&\Agriweather\EzpayInvoice\Crypto\EzpayCrypto */
        $crypto = mock(EzpayCrypto::class);
        $crypto->expects('setHashKey');
        $crypto->expects('setHashIv');
        $crypto->expects('encryptPostData')->with($expectedPostData)->andReturn('encrypted_data');
        $crypto->expects('verifyCheckCode');
        /** @var \Mockery\LegacyMockInterface&\Mockery\MockInterface&\Agriweather\EzpayInvoice\Contracts\HttpSender */
        $httpSender = mock(HttpSender::class);
        $httpSender->expects('send')->andReturn($response);

        (new InvoiceCreateBuilder($factory, $crypto, $httpSender))
            ->withOrder('Order123')
            ->forConsumer('John Doe')
            ->withTax(TaxType::TAXABLE, 5)
            ->withItem('商品名稱', quantity: 2, unit: '個', price: 100, amount: 200)
            ->withAmount(200, 5, 205)
            ->issue();
    });

    test('可以使用全部的參數', function () {
        $expectedPostData = [
            'RespondType' => 'JSON',
            'Version' => '1.5',
            'TimeStamp' => Carbon::now()->timestamp,
            'TransNum' => '1234567890',
            'MerchantOrderNo' => 'Order123',
            'Status' => '1',
            'Category' => 'B2B',
            'BuyerName' => '測試公司有限公司',
            'BuyerUBN' => '12345678',
            'BuyerAddress' => '台北市信義區信義路五段7號',
            'BuyerEmail' => 'business@company.com',
            'CarrierType' => '0',
            'CarrierNum' => '%2FABC.123',
            'LoveCode' => '12345678',
            'PrintFlag' => 'Y',
            'KioskPrintFlag' => '1',
            'TaxType' => '9',
            'TaxRate' => '5',
            'CustomsClearance' => '2',
            'Amt' => '200',
            'AmtSales' => '70',
            'AmtZero' => '80',
            'AmtFree' => '90',
            'TaxAmt' => '5',
            'TotalAmt' => '205',
            'ItemName' => '商品名稱',
            'ItemCount' => '2',
            'ItemUnit' => '個',
            'ItemPrice' => '100',
            'ItemAmt' => '200',
            'ItemTaxType' => '1',
            'Comment' => '這是一個測試發票',
        ];

        /** @var \Mockery\LegacyMockInterface&\Mockery\MockInterface&\Illuminate\Http\Client\Response */
        $response = mock(HttpClientResponse::class);
        $response->expects('json')->andReturn(['Status' => 'SUCCESS']);
        /** @var \Mockery\LegacyMockInterface&\Mockery\MockInterface&\Agriweather\EzpayInvoice\Factory */
        $factory = mock(Factory::class);
        $factory->expects('baseUrl')->andReturn('https://example.com/api/');
        $factory->shouldReceive('config')->andReturn('1234567890');
        /** @var \Mockery\LegacyMockInterface&\Mockery\MockInterface&\Agriweather\EzpayInvoice\Crypto\EzpayCrypto */
        $crypto = mock(EzpayCrypto::class);
        $crypto->expects('setHashKey');
        $crypto->expects('setHashIv');
        $crypto->expects('encryptPostData')->with($expectedPostData)->andReturn('encrypted_data');
        $crypto->expects('verifyCheckCode');
        /** @var \Mockery\LegacyMockInterface&\Mockery\MockInterface&\Agriweather\EzpayInvoice\Contracts\HttpSender */
        $httpSender = mock(HttpSender::class);
        $httpSender->expects('send')->andReturn($response);

        (new InvoiceCreateBuilder($factory, $crypto, $httpSender))
            ->withEzPayTransNumber('1234567890')
            ->withOrder('Order123')
            ->forBusiness('測試公司有限公司', '12345678')
            ->withAddress('台北市信義區信義路五段7號')
            ->withEmail('business@company.com')
            ->withCarrier(CarrierType::MOBILE, '/ABC.123')
            ->withLoveCode('12345678')
            ->withPrint()
            ->withKioskPrint()
            ->withTax(TaxType::MIXED, 5)
            ->withCustomsClearance(CustomsClearance::CUSTOMS)
            ->withItem('商品名稱', quantity: 2, unit: '個', price: 100, amount: 200, taxType: TaxType::TAXABLE)
            ->withMixedTaxAmount(70, 80, 90)
            ->withAmount(200, 5, 205)
            ->withComment('這是一個測試發票')
            ->issue();
    });

    test('可以設定應稅稅率', function () {
        $expectedPostData = [
            'RespondType' => 'JSON',
            'Version' => '1.5',
            'TimeStamp' => Carbon::now()->timestamp,
            'MerchantOrderNo' => '',
            'Status' => '1',
            'Category' => 'B2C',
            'BuyerName' => '',
            'PrintFlag' => 'Y',
            'TaxType' => '1',
            'TaxRate' => '5',
            'Amt' => '0',
            'TaxAmt' => '0',
            'TotalAmt' => '0',
            'ItemName' => '',
            'ItemCount' => '',
            'ItemUnit' => '',
            'ItemPrice' => '',
            'ItemAmt' => '',
        ];

        /** @var \Mockery\LegacyMockInterface&\Mockery\MockInterface&\Illuminate\Http\Client\Response */
        $response = mock(HttpClientResponse::class);
        $response->expects('json')->andReturn(['Status' => 'SUCCESS']);
        /** @var \Mockery\LegacyMockInterface&\Mockery\MockInterface&\Agriweather\EzpayInvoice\Factory */
        $factory = mock(Factory::class);
        $factory->expects('baseUrl')->andReturn('https://example.com/api/');
        $factory->shouldReceive('config')->andReturn('1234567890');
        /** @var \Mockery\LegacyMockInterface&\Mockery\MockInterface&\Agriweather\EzpayInvoice\Crypto\EzpayCrypto */
        $crypto = mock(EzpayCrypto::class);
        $crypto->expects('setHashKey');
        $crypto->expects('setHashIv');
        $crypto->expects('encryptPostData')->with($expectedPostData)->andReturn('encrypted_data');
        $crypto->expects('verifyCheckCode');
        /** @var \Mockery\LegacyMockInterface&\Mockery\MockInterface&\Agriweather\EzpayInvoice\Contracts\HttpSender */
        $httpSender = mock(HttpSender::class);
        $httpSender->expects('send')->andReturn($response);

        (new InvoiceCreateBuilder($factory, $crypto, $httpSender))
            ->withTax(TaxType::TAXABLE, 5)
            ->issue();
    });

    test('可以設定零稅率', function () {
        $expectedPostData = [
            'RespondType' => 'JSON',
            'Version' => '1.5',
            'TimeStamp' => Carbon::now()->timestamp,
            'MerchantOrderNo' => '',
            'Status' => '1',
            'Category' => 'B2C',
            'BuyerName' => '',
            'PrintFlag' => 'Y',
            'TaxType' => '2',
            'TaxRate' => '0',
            'Amt' => '0',
            'TaxAmt' => '0',
            'TotalAmt' => '0',
            'ItemName' => '',
            'ItemCount' => '',
            'ItemUnit' => '',
            'ItemPrice' => '',
            'ItemAmt' => '',
        ];

        /** @var \Mockery\LegacyMockInterface&\Mockery\MockInterface&\Illuminate\Http\Client\Response */
        $response = mock(HttpClientResponse::class);
        $response->expects('json')->andReturn(['Status' => 'SUCCESS']);
        /** @var \Mockery\LegacyMockInterface&\Mockery\MockInterface&\Agriweather\EzpayInvoice\Factory */
        $factory = mock(Factory::class);
        $factory->expects('baseUrl')->andReturn('https://example.com/api/');
        $factory->shouldReceive('config')->andReturn('1234567890');
        /** @var \Mockery\LegacyMockInterface&\Mockery\MockInterface&\Agriweather\EzpayInvoice\Crypto\EzpayCrypto */
        $crypto = mock(EzpayCrypto::class);
        $crypto->expects('setHashKey');
        $crypto->expects('setHashIv');
        $crypto->expects('encryptPostData')->with($expectedPostData)->andReturn('encrypted_data');
        $crypto->expects('verifyCheckCode');
        /** @var \Mockery\LegacyMockInterface&\Mockery\MockInterface&\Agriweather\EzpayInvoice\Contracts\HttpSender */
        $httpSender = mock(HttpSender::class);
        $httpSender->expects('send')->andReturn($response);

        (new InvoiceCreateBuilder($factory, $crypto, $httpSender))
            ->withTax(TaxType::ZERO_RATE)
            ->issue();
    });

    test('可以設定免稅', function () {
        $expectedPostData = [
            'RespondType' => 'JSON',
            'Version' => '1.5',
            'TimeStamp' => Carbon::now()->timestamp,
            'MerchantOrderNo' => '',
            'Status' => '1',
            'Category' => 'B2C',
            'BuyerName' => '',
            'PrintFlag' => 'Y',
            'TaxType' => '3',
            'TaxRate' => '0',
            'Amt' => '0',
            'TaxAmt' => '0',
            'TotalAmt' => '0',
            'ItemName' => '',
            'ItemCount' => '',
            'ItemUnit' => '',
            'ItemPrice' => '',
            'ItemAmt' => '',
        ];

        /** @var \Mockery\LegacyMockInterface&\Mockery\MockInterface&\Illuminate\Http\Client\Response */
        $response = mock(HttpClientResponse::class);
        $response->expects('json')->andReturn(['Status' => 'SUCCESS']);
        /** @var \Mockery\LegacyMockInterface&\Mockery\MockInterface&\Agriweather\EzpayInvoice\Factory */
        $factory = mock(Factory::class);
        $factory->expects('baseUrl')->andReturn('https://example.com/api/');
        $factory->shouldReceive('config')->andReturn('1234567890');
        /** @var \Mockery\LegacyMockInterface&\Mockery\MockInterface&\Agriweather\EzpayInvoice\Crypto\EzpayCrypto */
        $crypto = mock(EzpayCrypto::class);
        $crypto->expects('setHashKey');
        $crypto->expects('setHashIv');
        $crypto->expects('encryptPostData')->with($expectedPostData)->andReturn('encrypted_data');
        $crypto->expects('verifyCheckCode');
        /** @var \Mockery\LegacyMockInterface&\Mockery\MockInterface&\Agriweather\EzpayInvoice\Contracts\HttpSender */
        $httpSender = mock(HttpSender::class);
        $httpSender->expects('send')->andReturn($response);

        (new InvoiceCreateBuilder($factory, $crypto, $httpSender))
            ->withTax(TaxType::TAX_FREE)
            ->issue();
    });

    test('可以設定混合稅率，和各種混合稅率的銷售額', function () {
        $expectedPostData = ['RespondType' => 'JSON',
            'Version' => '1.5',
            'TimeStamp' => Carbon::now()->timestamp,
            'MerchantOrderNo' => '',
            'Status' => '1',
            'Category' => 'B2C',
            'BuyerName' => '',
            'PrintFlag' => 'Y',
            'TaxType' => '9',
            'TaxRate' => '5',
            'Amt' => '370',
            'AmtSales' => '200',
            'AmtZero' => '80',
            'AmtFree' => '90',
            'TaxAmt' => '19',
            'TotalAmt' => '389',
            'ItemName' => '商品名稱1|商品名稱2|商品名稱3',
            'ItemCount' => '2|1|1',
            'ItemUnit' => '個|個|個',
            'ItemPrice' => '100|80|90',
            'ItemAmt' => '200|80|90',
            'ItemTaxType' => '1|2|3',
        ];

        /** @var \Mockery\LegacyMockInterface&\Mockery\MockInterface&\Illuminate\Http\Client\Response */
        $response = mock(HttpClientResponse::class);
        $response->expects('json')->andReturn(['Status' => 'SUCCESS']);
        /** @var \Mockery\LegacyMockInterface&\Mockery\MockInterface&\Agriweather\EzpayInvoice\Factory */
        $factory = mock(Factory::class);
        $factory->expects('baseUrl')->andReturn('https://example.com/api/');
        $factory->shouldReceive('config')->andReturn('1234567890');
        /** @var \Mockery\LegacyMockInterface&\Mockery\MockInterface&\Agriweather\EzpayInvoice\Crypto\EzpayCrypto */
        $crypto = mock(EzpayCrypto::class);
        $crypto->expects('setHashKey');
        $crypto->expects('setHashIv');
        $crypto->expects('encryptPostData')->with($expectedPostData)->andReturn('encrypted_data');
        $crypto->expects('verifyCheckCode');
        /** @var \Mockery\LegacyMockInterface&\Mockery\MockInterface&\Agriweather\EzpayInvoice\Contracts\HttpSender */
        $httpSender = mock(HttpSender::class);
        $httpSender->expects('send')->andReturn($response);

        (new InvoiceCreateBuilder($factory, $crypto, $httpSender))
            ->withTax(TaxType::MIXED, 5)
            ->withItem('商品名稱1', quantity: 2, unit: '個', price: 100, amount: 200, taxType: TaxType::TAXABLE)
            ->withItem('商品名稱2', quantity: 1, unit: '個', price: 80, amount: 80, taxType: TaxType::ZERO_RATE)
            ->withItem('商品名稱3', quantity: 1, unit: '個', price: 90, amount: 90, taxType: TaxType::TAX_FREE)
            ->withMixedTaxAmount(200, 80, 90)
            ->withAmount(370, 19, 389)
            ->issue();
    });

    test('可以批次設定多個商品', function () {
        $expectedPostData = [
            'RespondType' => 'JSON',
            'Version' => '1.5',
            'TimeStamp' => Carbon::now()->timestamp,
            'MerchantOrderNo' => '',
            'Status' => '1',
            'Category' => 'B2C',
            'BuyerName' => '',
            'PrintFlag' => 'Y',
            'TaxType' => '1',
            'TaxRate' => '0',
            'Amt' => '370',
            'TaxAmt' => '0',
            'TotalAmt' => '370',
            'ItemName' => '商品名稱1|商品名稱2|商品名稱3',
            'ItemCount' => '2|1|1',
            'ItemUnit' => '個|個|個',
            'ItemPrice' => '100|80|90',
            'ItemAmt' => '200|80|90',
        ];

        /** @var \Mockery\LegacyMockInterface&\Mockery\MockInterface&\Illuminate\Http\Client\Response */
        $response = mock(HttpClientResponse::class);
        $response->expects('json')->andReturn(['Status' => 'SUCCESS']);
        /** @var \Mockery\LegacyMockInterface&\Mockery\MockInterface&\Agriweather\EzpayInvoice\Factory */
        $factory = mock(Factory::class);
        $factory->expects('baseUrl')->andReturn('https://example.com/api/');
        $factory->shouldReceive('config')->andReturn('1234567890');
        /** @var \Mockery\LegacyMockInterface&\Mockery\MockInterface&\Agriweather\EzpayInvoice\Crypto\EzpayCrypto */
        $crypto = mock(EzpayCrypto::class);
        $crypto->expects('setHashKey');
        $crypto->expects('setHashIv');
        $crypto->expects('encryptPostData')->with($expectedPostData)->andReturn('encrypted_data');
        $crypto->expects('verifyCheckCode');
        /** @var \Mockery\LegacyMockInterface&\Mockery\MockInterface&\Agriweather\EzpayInvoice\Contracts\HttpSender */
        $httpSender = mock(HttpSender::class);
        $httpSender->expects('send')->andReturn($response);

        (new InvoiceCreateBuilder($factory, $crypto, $httpSender))
            ->withItems([
                ['name' => '商品名稱1', 'quantity' => 2, 'unit' => '個', 'price' => 100, 'amount' => 200],
                ['name' => '商品名稱2', 'quantity' => 1, 'unit' => '個', 'price' => 80, 'amount' => 80],
                ['name' => '商品名稱3', 'quantity' => 1, 'unit' => '個', 'price' => 90, 'amount' => 90],
            ])
            ->withAmount(370, 0, 370)
            ->issue();
    });
});
