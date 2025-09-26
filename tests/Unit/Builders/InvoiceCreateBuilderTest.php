<?php

use Agriweather\EzPayInvoice\Builders\Invoice\CreateBuilder;
use Agriweather\EzPayInvoice\Contracts\HttpTransporter;
use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Enums\Invoice\CarrierType;
use Agriweather\EzPayInvoice\Enums\Invoice\CustomsClearance;
use Agriweather\EzPayInvoice\Enums\Invoice\ItemTaxType;
use Agriweather\EzPayInvoice\Enums\Invoice\TaxType;
use Agriweather\EzPayInvoice\Factory;
use Agriweather\EzPayInvoice\Options\Options;
use Carbon\Carbon;
use Illuminate\Http\Client\Response as HttpClientResponse;

beforeEach(function () {
    Carbon::setTestNow('2025-01-01 00:00:00');

    $this->response = mock(HttpClientResponse::class);
    $this->response->expects('json')->andReturn(['Status' => 'SUCCESS']);

    $this->factory = mock(Factory::class);
    $this->factory->expects('baseUrl')->andReturn('https://example.com/api/');
    $this->factory->shouldReceive('config')->andReturn('1234567890');
    $this->factory->expects('record');

    $this->crypto = mock(Crypto::class);
    $this->crypto->expects('setHashKey');
    $this->crypto->expects('setHashIv');
    $this->crypto->expects('encryptByAES')->andReturn('encrypted_data');
    $this->crypto->expects('verifyCheckCode');

    $this->httpTransporter = mock(HttpTransporter::class);
    $this->httpTransporter->expects('setTimeout');
    $this->httpTransporter->expects('send')->andReturn($this->response);
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

    (new CreateBuilder($this->factory, $this->crypto, $this->httpTransporter))
        ->withOrder('Order123')
        ->forConsumer('John Doe')
        ->withTax(TaxType::TAXABLE, 5)
        ->withItem('商品名稱', quantity: 2, unit: '個', price: 100, amount: 200)
        ->withAmount(200, 5, 205)
        ->onPreparedOptions(function (Options $options) use ($expectedPostData) {
            expect($options->toArray()['PostData_'])->toBe($expectedPostData);
        })
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
        'PrintFlag' => 'N',
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

    (new CreateBuilder($this->factory, $this->crypto, $this->httpTransporter))
        ->withEzPayTransNumber('1234567890')
        ->withOrder('Order123')
        ->forBusiness('測試公司有限公司', '12345678')
        ->withAddress('台北市信義區信義路五段7號')
        ->withEmail('business@company.com')
        ->withCarrier(CarrierType::MOBILE, '/ABC.123')
        ->withLoveCode('12345678')
        ->withKioskPrint()
        ->withTax(TaxType::MIXED, 5)
        ->withCustomsClearance(CustomsClearance::CUSTOMS)
        ->withItem('商品名稱', quantity: 2, unit: '個', price: 100, amount: 200, taxType: ItemTaxType::TAXABLE)
        ->withMixedTaxAmount(70, 80, 90)
        ->withAmount(200, 5, 205)
        ->withComment('這是一個測試發票')
        ->onPreparedOptions(function (Options $options) use ($expectedPostData) {
            expect($options->toArray()['PostData_'])->toBe($expectedPostData);
        })
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

    (new CreateBuilder($this->factory, $this->crypto, $this->httpTransporter))
        ->withTax(TaxType::TAXABLE, 5)
        ->onPreparedOptions(function (Options $options) use ($expectedPostData) {
            expect($options->toArray()['PostData_'])->toBe($expectedPostData);
        })
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

    (new CreateBuilder($this->factory, $this->crypto, $this->httpTransporter))
        ->withTax(TaxType::ZERO_RATE)
        ->onPreparedOptions(function (Options $options) use ($expectedPostData) {
            expect($options->toArray()['PostData_'])->toBe($expectedPostData);
        })
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

    (new CreateBuilder($this->factory, $this->crypto, $this->httpTransporter))
        ->withTax(TaxType::TAX_FREE)
        ->onPreparedOptions(function (Options $options) use ($expectedPostData) {
            expect($options->toArray()['PostData_'])->toBe($expectedPostData);
        })
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

    (new CreateBuilder($this->factory, $this->crypto, $this->httpTransporter))
        ->withTax(TaxType::MIXED, 5)
        ->withItem('商品名稱1', quantity: 2, unit: '個', price: 100, amount: 200, taxType: ItemTaxType::TAXABLE)
        ->withItem('商品名稱2', quantity: 1, unit: '個', price: 80, amount: 80, taxType: ItemTaxType::ZERO_RATE)
        ->withItem('商品名稱3', quantity: 1, unit: '個', price: 90, amount: 90, taxType: ItemTaxType::TAX_FREE)
        ->withMixedTaxAmount(200, 80, 90)
        ->withAmount(370, 19, 389)
        ->onPreparedOptions(function (Options $options) use ($expectedPostData) {
            expect($options->toArray()['PostData_'])->toBe($expectedPostData);
        })
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

    (new CreateBuilder($this->factory, $this->crypto, $this->httpTransporter))
        ->withItems([
            ['name' => '商品名稱1', 'quantity' => 2, 'unit' => '個', 'price' => 100, 'amount' => 200],
            ['name' => '商品名稱2', 'quantity' => 1, 'unit' => '個', 'price' => 80, 'amount' => 80],
            ['name' => '商品名稱3', 'quantity' => 1, 'unit' => '個', 'price' => 90, 'amount' => 90],
        ])
        ->withAmount(370, 0, 370)
        ->onPreparedOptions(function (Options $options) use ($expectedPostData) {
            expect($options->toArray()['PostData_'])->toBe($expectedPostData);
        })
        ->issue();
});
