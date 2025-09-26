<?php

use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Enums\Invoice\CarrierType;
use Agriweather\EzPayInvoice\Enums\Invoice\InvoiceCategory;
use Agriweather\EzPayInvoice\Enums\Invoice\InvoiceCreateStatus;
use Agriweather\EzPayInvoice\Enums\Invoice\InvoicePrintFlag;
use Agriweather\EzPayInvoice\Enums\Invoice\TaxType;
use Agriweather\EzPayInvoice\Facades\EzPayInvoice;
use Agriweather\EzPayInvoice\Options\Invoice\CreateOptions;
use Agriweather\EzPayInvoice\Options\Options;
use Agriweather\EzPayInvoice\Resources\Invoice;
use Agriweather\EzPayInvoice\Results\Invoice\CreateResult;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\partialMock;

test('發票開立 → 可以成功開立 B2C 發票', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptByAES')->andReturn('encrypted_data');
    $crypto->expects('verifyCheckCode')->andReturnNull();

    Http::fake([
        '*' => Http::response([
            'Status' => 'SUCCESS',
            'Message' => '發票開立成功',
            'Result' => json_encode([
                'CheckCode' => '123456789',
                'MerchantID' => '111335678',
                'MerchantOrderNo' => 'Order001',
                'InvoiceNumber' => 'GG72002017',
                'TotalAmt' => 1050,
                'InvoiceTransNo' => '25072515224376654',
                'RandomNum' => '1234',
                'CreateTime' => '2025-01-01 00:00:00',
                'BarCode' => '11408GG720020179356',
                'QRcodeL' => 'GG7200201711407259356000003e80000041a0000000087612689JeS9LvMqldHvkH5bIDsJXw==:**********:1:1:1:測試商品:1:1000',
                'QRcodeR' => '**',
            ]),
        ], 200),
    ]);

    $result = EzPayInvoice::invoice()
        ->create()
        ->withOrder('Order001')
        ->forConsumer('John Doe')
        ->withEmail('customer@example.com')
        ->withAddress('台北市信義區信義路五段7號')
        ->withItem('測試商品', quantity: 1, unit: '個', price: 1000, amount: 1000)
        ->withTax(TaxType::TAXABLE, 5)
        ->withAmount(1000, 50, 1050)
        ->onPreparedOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'RespondType' => 'JSON',
                'Version' => '1.5',
                'TimeStamp' => Carbon::now()->timestamp,
                'MerchantOrderNo' => 'Order001',
                'Status' => '1',
                'Category' => 'B2C',
                'BuyerName' => 'John Doe',
                'BuyerAddress' => '台北市信義區信義路五段7號',
                'BuyerEmail' => 'customer@example.com',
                'PrintFlag' => 'Y',
                'TaxType' => '1',
                'TaxRate' => '5',
                'Amt' => '1000',
                'TaxAmt' => '50',
                'TotalAmt' => '1050',
                'ItemName' => '測試商品',
                'ItemCount' => '1',
                'ItemUnit' => '個',
                'ItemPrice' => '1000',
                'ItemAmt' => '1000',
            ]);
        })
        ->issue();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api/invoice_issue';
    });

    expect($result)->toBeInstanceOf(CreateResult::class)
        ->and($result->checkCode())->toBe('123456789')
        ->and($result->orderNo())->toBe('Order001')
        ->and($result->invoiceNumber())->toBe('GG72002017')
        ->and($result->totalAmount())->toBe(1050)
        ->and($result->randomNumber())->toBe('1234');
});

test('發票開立 → 可以成功開立 B2B 發票', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptByAES')->andReturn('encrypted_data');
    $crypto->expects('verifyCheckCode')->andReturnNull();

    Http::fake([
        '*' => Http::response([
            'Status' => 'SUCCESS',
            'Message' => '發票開立成功',
            'Result' => json_encode([
                'CheckCode' => '123456789',
                'MerchantID' => '111335678',
                'MerchantOrderNo' => 'Order002',
                'InvoiceNumber' => 'GG72002018',
                'TotalAmt' => 1050,
                'InvoiceTransNo' => '25072516191017210',
                'RandomNum' => '1234',
                'CreateTime' => '2025-01-01 00:00:00',
                'BarCode' => '11408GG720020184793',
                'QRcodeL' => 'GG7200201811407254793000003e80000041a1234567887612689D2cOPJmMB8DEjVt8PLTD0w==:**********:2:2:1:商品A:2:300:商品B:1:400',
                'QRcodeR' => '**',
            ]),
        ], 200),
    ]);

    $result = EzPayInvoice::invoice()
        ->create()
        ->withOrder('Order002')
        ->forBusiness('測試公司有限公司', '12345678')
        ->withEmail('business@company.com')
        ->withAddress('台北市信義區信義路五段7號')
        ->withItem('商品A', quantity: 2, unit: '個', price: 300, amount: 600)
        ->withItem('商品B', quantity: 1, unit: '個', price: 400, amount: 400)
        ->withTax(TaxType::TAXABLE, 5)
        ->withAmount(1000, 50, 1050)
        ->onPreparedOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'RespondType' => 'JSON',
                'Version' => '1.5',
                'TimeStamp' => Carbon::now()->timestamp,
                'MerchantOrderNo' => 'Order002',
                'Status' => '1',
                'Category' => 'B2B',
                'BuyerName' => '測試公司有限公司',
                'BuyerUBN' => '12345678',
                'BuyerAddress' => '台北市信義區信義路五段7號',
                'BuyerEmail' => 'business@company.com',
                'PrintFlag' => 'Y',
                'TaxType' => '1',
                'TaxRate' => '5',
                'Amt' => '1000',
                'TaxAmt' => '50',
                'TotalAmt' => '1050',
                'ItemName' => '商品A|商品B',
                'ItemCount' => '2|1',
                'ItemUnit' => '個|個',
                'ItemPrice' => '300|400',
                'ItemAmt' => '600|400',
            ]);
        })
        ->issue();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api/invoice_issue';
    });

    expect($result)->toBeInstanceOf(CreateResult::class)
        ->and($result->checkCode())->toBe('123456789')
        ->and($result->orderNo())->toBe('Order002')
        ->and($result->invoiceNumber())->toBe('GG72002018')
        ->and($result->totalAmount())->toBe(1050)
        ->and($result->randomNumber())->toBe('1234');
});

test('發票開立 → 可以開立載具發票', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptByAES')->andReturn('encrypted_data');
    $crypto->expects('verifyCheckCode')->andReturnNull();

    Http::fake([
        '*' => Http::response([
            'Status' => 'SUCCESS',
            'Message' => '發票開立成功',
            'Result' => json_encode([
                'CheckCode' => '123456789',
                'MerchantID' => '111335678',
                'MerchantOrderNo' => 'Order003',
                'InvoiceNumber' => 'GG72002019',
                'TotalAmt' => 525,
                'InvoiceTransNo' => '25072516310082443',
                'RandomNum' => '1234',
                'CreateTime' => '2025-01-01 00:00:00',
            ]),
        ], 200),
    ]);

    $result = EzPayInvoice::invoice()
        ->create()
        ->withOrder('Order003')
        ->forConsumer('載具客戶')
        ->withCarrier(CarrierType::MOBILE, '/ABC.123')
        ->withItem('載具商品', quantity: 1, unit: '個', price: 500, amount: 500)
        ->withTax(TaxType::TAXABLE, 5)
        ->withAmount(500, 25, 525)
        ->onPreparedOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'RespondType' => 'JSON',
                'Version' => '1.5',
                'TimeStamp' => Carbon::now()->timestamp,
                'MerchantOrderNo' => 'Order003',
                'Status' => '1',
                'Category' => 'B2C',
                'BuyerName' => '載具客戶',
                'CarrierType' => '0',
                'CarrierNum' => '%2FABC.123',
                'PrintFlag' => 'N',
                'TaxType' => '1',
                'TaxRate' => '5',
                'Amt' => '500',
                'TaxAmt' => '25',
                'TotalAmt' => '525',
                'ItemName' => '載具商品',
                'ItemCount' => '1',
                'ItemUnit' => '個',
                'ItemPrice' => '500',
                'ItemAmt' => '500',
            ]);
        })
        ->issue();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api/invoice_issue';
    });

    expect($result)->toBeInstanceOf(CreateResult::class)
        ->and($result->orderNo())->toBe('Order003');
});

test('發票開立 → 可以開立發票並等待觸發', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptByAES')->andReturn('encrypted_data');
    $crypto->expects('verifyCheckCode')->andReturnNull();

    Http::fake([
        '*' => Http::response([
            'Status' => 'SUCCESS',
            'Message' => '發票開立成功',
            'Result' => json_encode([
                'CheckCode' => '123456789',
                'MerchantID' => '111335678',
                'MerchantOrderNo' => 'Order004',
                'InvoiceNumber' => '',
                'TotalAmt' => 210,
                'InvoiceTransNo' => '25072516510985216',
                'RandomNum' => '1234',
                'CreateTime' => '',
            ]),
        ], 200),
    ]);

    $result = EzPayInvoice::invoice()
        ->create()
        ->withOrder('Order004')
        ->forConsumer('等待觸發客戶')
        ->withItem('等待觸發商品', quantity: 1, unit: '個', price: 200, amount: 200)
        ->withTax(TaxType::TAXABLE, 5)
        ->withAmount(200, 10, 210)
        ->onPreparedOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'RespondType' => 'JSON',
                'Version' => '1.5',
                'TimeStamp' => Carbon::now()->timestamp,
                'MerchantOrderNo' => 'Order004',
                'Status' => '0',
                'Category' => 'B2C',
                'BuyerName' => '等待觸發客戶',
                'PrintFlag' => 'Y',
                'TaxType' => '1',
                'TaxRate' => '5',
                'Amt' => '200',
                'TaxAmt' => '10',
                'TotalAmt' => '210',
                'ItemName' => '等待觸發商品',
                'ItemCount' => '1',
                'ItemUnit' => '個',
                'ItemPrice' => '200',
                'ItemAmt' => '200',
            ]);
        })
        ->deferIssue();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api/invoice_issue';
    });

    expect($result)->toBeInstanceOf(CreateResult::class)
        ->and($result->orderNo())->toBe('Order004')
        ->and($result->invoiceNumber())->toBeNull()
        ->and($result->createTime())->toBeNull();
});

test('發票開立 → 可以預約開立發票', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptByAES')->andReturn('encrypted_data');
    $crypto->expects('verifyCheckCode')->andReturnNull();

    Http::fake([
        '*' => Http::response([
            'Status' => 'SUCCESS',
            'Message' => '發票開立成功',
            'Result' => json_encode([
                'CheckCode' => '123456789',
                'MerchantID' => '111335678',
                'MerchantOrderNo' => 'Order005',
                'InvoiceNumber' => '',
                'TotalAmt' => 210,
                'InvoiceTransNo' => '25072516392250538',
                'RandomNum' => '1234',
                'CreateTime' => '',
            ]),
        ], 200),
    ]);

    $result = EzPayInvoice::invoice()
        ->create()
        ->withOrder('Order005')
        ->forConsumer('預約客戶')
        ->withItem('預約商品', quantity: 1, unit: '個', price: 200, amount: 200)
        ->withTax(TaxType::TAXABLE, 5)
        ->withAmount(200, 10, 210)
        ->onPreparedOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'RespondType' => 'JSON',
                'Version' => '1.5',
                'TimeStamp' => Carbon::now()->timestamp,
                'MerchantOrderNo' => 'Order005',
                'Status' => '3',
                'CreateStatusTime' => '2024-12-01',
                'Category' => 'B2C',
                'BuyerName' => '預約客戶',
                'PrintFlag' => 'Y',
                'TaxType' => '1',
                'TaxRate' => '5',
                'Amt' => '200',
                'TaxAmt' => '10',
                'TotalAmt' => '210',
                'ItemName' => '預約商品',
                'ItemCount' => '1',
                'ItemUnit' => '個',
                'ItemPrice' => '200',
                'ItemAmt' => '200',
            ]);
        })
        ->scheduleAt('2024-12-01');

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api/invoice_issue';
    });

    expect($result)->toBeInstanceOf(CreateResult::class)
        ->and($result->orderNo())->toBe('Order005')
        ->and($result->invoiceNumber())->toBeNull()
        ->and($result->createTime())->toBeNull();
});

test('發票開立 → 模擬開立 B2C 發票', function () {
    EzPayInvoice::fake([
        CreateResult::make([
            'Status' => 'SUCCESS',
            'Message' => '發票開立成功',
            'Result' => [
                'CheckCode' => '123456789',
                'MerchantID' => '111335678',
                'MerchantOrderNo' => 'Order001',
                'InvoiceNumber' => 'GG72002017',
                'TotalAmt' => 1050,
                'InvoiceTransNo' => '25072515224376654',
                'RandomNum' => '1234',
                'CreateTime' => '2025-01-01 00:00:00',
                'BarCode' => '11408GG720020179356',
                'QRcodeL' => 'GG7200201711407259356000003e80000041a0000000087612689JeS9LvMqldHvkH5bIDsJXw==:**********:1:1:1:測試商品:1:1000',
                'QRcodeR' => '**',
            ],
        ]),
    ]);

    $result = EzPayInvoice::invoice()
        ->create()
        ->withOrder('Order001')
        ->forConsumer('John Doe')
        ->withEmail('customer@example.com')
        ->withAddress('台北市信義區信義路五段7號')
        ->withItem('測試商品', quantity: 1, unit: '個', price: 1000, amount: 1000)
        ->withTax(TaxType::TAXABLE, 5)
        ->withAmount(1000, 50, 1050)
        ->issue();

    EzPayInvoice::assertSent(Invoice::class, 'create', function (CreateOptions $options) {
        return $options->orderNo === 'Order001'
            && $options->category === InvoiceCategory::B2C
            && $options->buyerName === 'John Doe'
            && $options->buyerEmail === 'customer@example.com'
            && $options->buyerAddress === '台北市信義區信義路五段7號'
            && $options->hasItem('測試商品', quantity: 1, unit: '個', price: 1000, amount: 1000)
            && $options->taxType === TaxType::TAXABLE
            && $options->taxRate === 5.0
            && $options->totalAmount === 1050;
    });

    expect($result)->toBeInstanceOf(CreateResult::class)
        ->and($result->checkCode())->toBe('123456789')
        ->and($result->orderNo())->toBe('Order001')
        ->and($result->invoiceNumber())->toBe('GG72002017')
        ->and($result->totalAmount())->toBe(1050)
        ->and($result->randomNumber())->toBe('1234');
});

test('發票開立 → 模擬開立 B2B 發票', function () {
    EzPayInvoice::fake([
        CreateResult::make([
            'Status' => 'SUCCESS',
            'Message' => '發票開立成功',
            'Result' => [
                'CheckCode' => '123456789',
                'MerchantID' => '111335678',
                'MerchantOrderNo' => 'Order002',
                'InvoiceNumber' => 'GG72002018',
                'TotalAmt' => 1050,
                'InvoiceTransNo' => '25072516191017210',
                'RandomNum' => '1234',
                'CreateTime' => '2025-01-01 00:00:00',
                'BarCode' => '11408GG720020184793',
                'QRcodeL' => 'GG7200201811407254793000003e80000041a1234567887612689D2cOPJmMB8DEjVt8PLTD0w==:**********:2:2:1:商品A:2:300:商品B:1:400',
                'QRcodeR' => '**',
            ],
        ]),
    ]);

    $result = EzPayInvoice::invoice()
        ->create()
        ->withOrder('Order002')
        ->forBusiness('測試公司有限公司', '12345678')
        ->withEmail('business@company.com')
        ->withAddress('台北市信義區信義路五段7號')
        ->withItem('商品A', quantity: 2, unit: '個', price: 300, amount: 600)
        ->withItem('商品B', quantity: 1, unit: '個', price: 400, amount: 400)
        ->withTax(TaxType::TAXABLE, 5)
        ->withAmount(1000, 50, 1050)
        ->issue();

    EzPayInvoice::assertSent(Invoice::class, 'create', function (CreateOptions $options) {
        return $options->orderNo === 'Order002'
            && $options->category === InvoiceCategory::B2B
            && $options->buyerName === '測試公司有限公司'
            && $options->buyerTaxIdNumber === '12345678'
            && $options->buyerEmail === 'business@company.com'
            && $options->buyerAddress === '台北市信義區信義路五段7號'
            && $options->hasItem('商品A', quantity: 2, unit: '個', price: 300, amount: 600)
            && $options->hasItem('商品B', quantity: 1, unit: '個', price: 400, amount: 400)
            && $options->taxType === TaxType::TAXABLE
            && $options->taxRate === 5.0
            && $options->totalAmount === 1050;
    });

    expect($result)->toBeInstanceOf(CreateResult::class)
        ->and($result->checkCode())->toBe('123456789')
        ->and($result->orderNo())->toBe('Order002')
        ->and($result->invoiceNumber())->toBe('GG72002018')
        ->and($result->totalAmount())->toBe(1050)
        ->and($result->randomNumber())->toBe('1234');
});

test('發票開立 → 模擬開立載具發票', function () {
    EzPayInvoice::fake([
        CreateResult::make([
            'Status' => 'SUCCESS',
            'Message' => '發票開立成功',
            'Result' => [
                'CheckCode' => '123456789',
                'MerchantID' => '111335678',
                'MerchantOrderNo' => 'Order003',
                'InvoiceNumber' => 'GG72002019',
                'TotalAmt' => 525,
                'InvoiceTransNo' => '25072516310082443',
                'RandomNum' => '1234',
                'CreateTime' => '2025-01-01 00:00:00',
            ],
        ]),
    ]);

    $result = EzPayInvoice::invoice()
        ->create()
        ->withOrder('Order003')
        ->forConsumer('載具客戶')
        ->withCarrier(CarrierType::MOBILE, '/ABC.123')
        ->withItem('載具商品', quantity: 1, unit: '個', price: 500, amount: 500)
        ->withTax(TaxType::TAXABLE, 5)
        ->withAmount(500, 25, 525)
        ->issue();

    EzPayInvoice::assertSent(Invoice::class, 'create', function (CreateOptions $options) {
        return $options->orderNo === 'Order003'
            && $options->category === InvoiceCategory::B2C
            && $options->buyerName === '載具客戶'
            && $options->carrierType === CarrierType::MOBILE
            && $options->carrierNumber === '/ABC.123'
            && $options->printFlag === InvoicePrintFlag::NO
            && $options->hasItem('載具商品', quantity: 1, unit: '個', price: 500, amount: 500)
            && $options->taxType === TaxType::TAXABLE
            && $options->taxRate === 5.0
            && $options->totalAmount === 525;
    });

    expect($result)->toBeInstanceOf(CreateResult::class)
        ->and($result->orderNo())->toBe('Order003');
});

test('發票開立 → 模擬開立發票並等待觸發', function () {
    EzPayInvoice::fake([
        CreateResult::make([
            'Status' => 'SUCCESS',
            'Message' => '發票開立成功',
            'Result' => [
                'CheckCode' => '123456789',
                'MerchantID' => '111335678',
                'MerchantOrderNo' => 'Order004',
                'InvoiceNumber' => '',
                'TotalAmt' => 210,
                'InvoiceTransNo' => '25072516510985216',
                'RandomNum' => '1234',
                'CreateTime' => '',
            ],
        ]),
    ]);

    $result = EzPayInvoice::invoice()
        ->create()
        ->withOrder('Order004')
        ->forConsumer('等待觸發客戶')
        ->withItem('等待觸發商品', quantity: 1, unit: '個', price: 200, amount: 200)
        ->withTax(TaxType::TAXABLE, 5)
        ->withAmount(200, 10, 210)
        ->deferIssue();

    EzPayInvoice::assertSent(Invoice::class, 'create', function (CreateOptions $options) {
        return $options->orderNo === 'Order004'
            && $options->category === InvoiceCategory::B2C
            && $options->buyerName === '等待觸發客戶'
            && $options->status === InvoiceCreateStatus::DEFERRED
            && $options->hasItem('等待觸發商品', quantity: 1, unit: '個', price: 200, amount: 200)
            && $options->taxType === TaxType::TAXABLE
            && $options->taxRate === 5.0
            && $options->totalAmount === 210;
    });

    expect($result)->toBeInstanceOf(CreateResult::class)
        ->and($result->orderNo())->toBe('Order004')
        ->and($result->invoiceNumber())->toBeNull()
        ->and($result->createTime())->toBeNull();
});

test('發票開立 → 模擬預約開立發票', function () {
    EzPayInvoice::fake([
        CreateResult::make([
            'Status' => 'SUCCESS',
            'Message' => '發票開立成功',
            'Result' => [
                'CheckCode' => '123456789',
                'MerchantID' => '111335678',
                'MerchantOrderNo' => 'Order005',
                'InvoiceNumber' => '',
                'TotalAmt' => 210,
                'InvoiceTransNo' => '25072516392250538',
                'RandomNum' => '1234',
                'CreateTime' => '',
            ],
        ]),
    ]);

    $result = EzPayInvoice::invoice()
        ->create()
        ->withOrder('Order005')
        ->forConsumer('預約客戶')
        ->withItem('預約商品', quantity: 1, unit: '個', price: 200, amount: 200)
        ->withTax(TaxType::TAXABLE, 5)
        ->withAmount(200, 10, 210)
        ->scheduleAt('2024-12-01');

    EzPayInvoice::assertSent(Invoice::class, 'create', function (CreateOptions $options) {
        return $options->orderNo === 'Order005'
            && $options->category === InvoiceCategory::B2C
            && $options->buyerName === '預約客戶'
            && $options->status === InvoiceCreateStatus::SCHEDULED
            && $options->createDate === '2024-12-01'
            && $options->hasItem('預約商品', quantity: 1, unit: '個', price: 200, amount: 200)
            && $options->taxType === TaxType::TAXABLE
            && $options->taxRate === 5.0
            && $options->totalAmount === 210;
    });

    expect($result)->toBeInstanceOf(CreateResult::class)
        ->and($result->orderNo())->toBe('Order005')
        ->and($result->invoiceNumber())->toBeNull()
        ->and($result->createTime())->toBeNull();
});
