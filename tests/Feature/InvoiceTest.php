<?php

use Agriweather\EzpayInvoice\Crypto\EzpayCrypto;
use Agriweather\EzpayInvoice\Enums\CarrierType;
use Agriweather\EzpayInvoice\Enums\TaxType;
use Agriweather\EzpayInvoice\Facades\EzpayInvoice;
use Agriweather\EzpayInvoice\Results\InvoiceCreateResult;
use Agriweather\EzpayInvoice\Results\InvoiceQueryResult;
use Agriweather\EzpayInvoice\Results\InvoiceQueryUrlResult;
use Agriweather\EzpayInvoice\Results\Result;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\partialMock;

describe('發票功能測試', function () {
    describe('發票開立流程', function () {
        test('可以成功開立 B2C 發票', function () {
            $ezpayCrypto = partialMock(EzpayCrypto::class);
            $ezpayCrypto->expects('encryptPostData')->with([
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
            ])->andReturn('encrypted_data');
            $ezpayCrypto->expects('verifyCheckCode')->andReturnNull();

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

            $result = EzpayInvoice::invoice()
                ->create()
                ->withOrder('Order001')
                ->forConsumer('John Doe')
                ->withEmail('customer@example.com')
                ->withAddress('台北市信義區信義路五段7號')
                ->withItem('測試商品', quantity: 1, unit: '個', price: 1000, amount: 1000)
                ->withTax(TaxType::TAXABLE, 5)
                ->withAmount(1000, 50, 1050)
                ->issue();

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api/invoice_issue';
            });

            expect($result)->toBeInstanceOf(InvoiceCreateResult::class)
                ->and($result->checkCode())->toBe('123456789')
                ->and($result->orderNo())->toBe('Order001')
                ->and($result->invoiceNumber())->toBe('GG72002017')
                ->and($result->totalAmount())->toBe(1050)
                ->and($result->randomNumber())->toBe('1234');
        });

        test('可以成功開立 B2B 發票', function () {
            $ezpayCrypto = partialMock(EzpayCrypto::class);
            $ezpayCrypto->expects('encryptPostData')->with([
                'RespondType' => 'JSON',
                'Version' => '1.5',
                'TimeStamp' => Carbon::now()->timestamp,
                'Status' => '1',
                'PrintFlag' => 'Y',
                'MerchantOrderNo' => 'Order002',
                'Category' => 'B2B',
                'BuyerName' => '測試公司有限公司',
                'BuyerUBN' => '12345678',
                'BuyerAddress' => '台北市信義區信義路五段7號',
                'BuyerEmail' => 'business@company.com',
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
            ])->andReturn('encrypted_data');
            $ezpayCrypto->expects('verifyCheckCode')->andReturnNull();

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

            $result = EzpayInvoice::invoice()
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

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api/invoice_issue';
            });

            expect($result)->toBeInstanceOf(InvoiceCreateResult::class)
                ->and($result->checkCode())->toBe('123456789')
                ->and($result->orderNo())->toBe('Order002')
                ->and($result->invoiceNumber())->toBe('GG72002018')
                ->and($result->totalAmount())->toBe(1050)
                ->and($result->randomNumber())->toBe('1234');
        });

        test('可以開立載具發票', function () {
            $ezpayCrypto = partialMock(EzpayCrypto::class);
            $ezpayCrypto->expects('encryptPostData')->with([
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
            ])->andReturn('encrypted_data');
            $ezpayCrypto->expects('verifyCheckCode')->andReturnNull();

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

            $result = EzpayInvoice::invoice()
                ->create()
                ->withOrder('Order003')
                ->forConsumer('載具客戶')
                ->withCarrier(CarrierType::MOBILE, '/ABC.123')
                ->withoutPrint()
                ->withItem('載具商品', quantity: 1, unit: '個', price: 500, amount: 500)
                ->withTax(TaxType::TAXABLE, 5)
                ->withAmount(500, 25, 525)
                ->issue();

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api/invoice_issue';
            });

            expect($result)->toBeInstanceOf(InvoiceCreateResult::class)
                ->and($result->orderNo())->toBe('Order003');
        });

        test('可以開立發票並等待觸發', function () {
            $ezpayCrypto = partialMock(EzpayCrypto::class);
            $ezpayCrypto->expects('encryptPostData')->with([
                'RespondType' => 'JSON',
                'Version' => '1.5',
                'TimeStamp' => Carbon::now()->timestamp,
                'Status' => '0',
                'PrintFlag' => 'Y',
                'MerchantOrderNo' => 'Order004',
                'Category' => 'B2C',
                'BuyerName' => '等待觸發客戶',
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
            ])->andReturn('encrypted_data');
            $ezpayCrypto->expects('verifyCheckCode')->andReturnNull();

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

            $result = EzpayInvoice::invoice()
                ->create()
                ->withOrder('Order004')
                ->forConsumer('等待觸發客戶')
                ->withItem('等待觸發商品', quantity: 1, unit: '個', price: 200, amount: 200)
                ->withTax(TaxType::TAXABLE, 5)
                ->withAmount(200, 10, 210)
                ->deferIssue();

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api/invoice_issue';
            });

            expect($result)->toBeInstanceOf(InvoiceCreateResult::class)
                ->and($result->orderNo())->toBe('Order004')
                ->and($result->invoiceNumber())->toBeNull()
                ->and($result->createTime())->toBeNull();
        });

        test('可以預約開立發票', function () {
            $ezpayCrypto = partialMock(EzpayCrypto::class);
            $ezpayCrypto->expects('encryptPostData')->with([
                'RespondType' => 'JSON',
                'Version' => '1.5',
                'TimeStamp' => Carbon::now()->timestamp,
                'Status' => '3',
                'PrintFlag' => 'Y',
                'MerchantOrderNo' => 'Order005',
                'CreateStatusTime' => '2024-12-01',
                'Category' => 'B2C',
                'BuyerName' => '預約客戶',
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
            ])->andReturn('encrypted_data');
            $ezpayCrypto->expects('verifyCheckCode')->andReturnNull();

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

            $result = EzpayInvoice::invoice()
                ->create()
                ->withOrder('Order005')
                ->forConsumer('預約客戶')
                ->withItem('預約商品', quantity: 1, unit: '個', price: 200, amount: 200)
                ->withTax(TaxType::TAXABLE, 5)
                ->withAmount(200, 10, 210)
                ->scheduleAt('2024-12-01');

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api/invoice_issue';
            });

            expect($result)->toBeInstanceOf(InvoiceCreateResult::class)
                ->and($result->orderNo())->toBe('Order005')
                ->and($result->invoiceNumber())->toBeNull()
                ->and($result->createTime())->toBeNull();
        });
    });

    describe('發票查詢功能', function () {
        test('可以透過發票號碼及隨機碼查詢發票', function () {
            $ezpayCrypto = partialMock(EzpayCrypto::class);
            $ezpayCrypto->expects('encryptPostData')->with([
                'RespondType' => 'JSON',
                'Version' => '1.3',
                'TimeStamp' => Carbon::now()->timestamp,
                'SearchType' => '0',
                'MerchantOrderNo' => '',
                'TotalAmt' => '0',
                'InvoiceNumber' => 'GG72002017',
                'RandomNum' => '1234',
            ])->andReturn('encrypted_data');
            $ezpayCrypto->expects('verifyCheckCode')->andReturnNull();

            Http::fake([
                '*' => Http::response([
                    'Status' => 'SUCCESS',
                    'Message' => '查詢成功',
                    'Result' => json_encode([
                        'MerchantID' => '111335678',
                        'InvoiceTransNo' => '25072515224376654',
                        'MerchantOrderNo' => 'Order001',
                        'InvoiceNumber' => 'GG72002017',
                        'RandomNum' => '1234',
                        'BuyerName' => 'John Doe',
                        'BuyerUBN' => '',
                        'BuyerAddress' => '台北市信義區',
                        'BuyerPhone' => '',
                        'BuyerEmail' => 'customer@example.com',
                        'InvoiceType' => '07',
                        'Category' => 'B2C',
                        'TaxType' => '1',
                        'TaxRate' => '0.05000',
                        'Amt' => '1000',
                        'TaxAmt' => '50',
                        'TotalAmt' => '1050',
                        'LoveCode' => '',
                        'PrintFlag' => 'Y',
                        'CreateTime' => '2025-01-01 00:00:00',
                        'ItemDetail' => json_encode([
                            [
                                'ItemName' => '測試商品',
                                'ItemCount' => '1',
                                'ItemWord' => '個',
                                'ItemPrice' => '1000',
                                'ItemAmount' => '1000',
                                'ItemTaxType' => '',
                                'ItemNum' => '1',
                                'ItemRemark' => '',
                                'RelateNumber' => '',
                            ],
                        ]),
                        'InvoiceStatus' => '1',
                        'CreateStatusTime' => '',
                        'UploadStatus' => '1',
                        'CheckCode' => '123456789',
                        'CarrierType' => '',
                        'CarrierNum' => '',
                        'BarCode' => '11408GG720020179356',
                        'QRcodeL' => 'GG7200201711407259356000003e80000041a0000000087612689JeS9LvMqldHvkH5bIDsJXw==:**********:1:1:1:測試商品:1:1000',
                        'QRcodeR' => '**',
                        'KioskPrintFlag' => '',
                    ]),
                ], 200),
            ]);

            $invoiceResult = EzpayInvoice::invoice()
                ->query()
                ->withInvoice('GG72002017')
                ->withRandomNumber('1234')
                ->get();

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api/invoice_search';
            });

            expect($invoiceResult)->toBeInstanceOf(InvoiceQueryResult::class)
                ->and($invoiceResult->invoiceNumber())->toBe('GG72002017')
                ->and($invoiceResult->orderNo())->toBe('Order001')
                ->and($invoiceResult->totalAmount())->toBe(1050)
                ->and($invoiceResult->buyerName())->toBe('John Doe')
                ->and($invoiceResult->buyerEmail())->toBe('customer@example.com');
        });

        test('可以透過訂單編號及發票金額查詢發票', function () {
            $ezpayCrypto = partialMock(EzpayCrypto::class);
            $ezpayCrypto->expects('encryptPostData')->with([
                'RespondType' => 'JSON',
                'Version' => '1.3',
                'TimeStamp' => Carbon::now()->timestamp,
                'SearchType' => '1',
                'MerchantOrderNo' => 'Order001',
                'TotalAmt' => '1050',
                'InvoiceNumber' => '',
                'RandomNum' => '',
            ])->andReturn('encrypted_data');
            $ezpayCrypto->expects('verifyCheckCode')->andReturnNull();

            Http::fake([
                '*' => Http::response([
                    'Status' => 'SUCCESS',
                    'Message' => '查詢成功',
                    'Result' => json_encode([
                        'MerchantID' => '111335678',
                        'InvoiceTransNo' => '25072515224376654',
                        'MerchantOrderNo' => 'Order001',
                        'InvoiceNumber' => 'GG72002017',
                        'RandomNum' => '1234',
                        'BuyerName' => 'John Doe',
                        'BuyerUBN' => '',
                        'BuyerAddress' => '台北市信義區',
                        'BuyerPhone' => '',
                        'BuyerEmail' => 'customer@example.com',
                        'InvoiceType' => '07',
                        'Category' => 'B2C',
                        'TaxType' => '1',
                        'TaxRate' => '0.05000',
                        'Amt' => '1000',
                        'TaxAmt' => '50',
                        'TotalAmt' => '1050',
                        'LoveCode' => '',
                        'PrintFlag' => 'Y',
                        'CreateTime' => '2025-01-01 00:00:00',
                        'ItemDetail' => json_encode([
                            [
                                'ItemName' => '測試商品',
                                'ItemCount' => '1',
                                'ItemWord' => '個',
                                'ItemPrice' => '1000',
                                'ItemAmount' => '1000',
                                'ItemTaxType' => '',
                                'ItemNum' => '1',
                                'ItemRemark' => '',
                                'RelateNumber' => '',
                            ],
                        ]),
                        'InvoiceStatus' => '1',
                        'CreateStatusTime' => '',
                        'UploadStatus' => '1',
                        'CheckCode' => '123456789',
                        'CarrierType' => '',
                        'CarrierNum' => '',
                        'BarCode' => '11408GG720020179356',
                        'QRcodeL' => 'GG7200201711407259356000003e80000041a0000000087612689JeS9LvMqldHvkH5bIDsJXw==:**********:1:1:1:測試商品:1:1000',
                        'QRcodeR' => '**',
                        'KioskPrintFlag' => '',
                    ]),
                ], 200),
            ]);

            $invoiceResult = EzpayInvoice::invoice()
                ->query()
                ->withOrder('Order001')
                ->withTotalAmount(1050)
                ->get();

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api/invoice_search';
            });

            expect($invoiceResult)->toBeInstanceOf(InvoiceQueryResult::class)
                ->and($invoiceResult->invoiceNumber())->toBe('GG72002017')
                ->and($invoiceResult->orderNo())->toBe('Order001')
                ->and($invoiceResult->totalAmount())->toBe(1050)
                ->and($invoiceResult->buyerName())->toBe('John Doe')
                ->and($invoiceResult->buyerEmail())->toBe('customer@example.com');
        });

        test('可以跳轉到 ezPay 平台查詢發票', function () {
            $ezpayCrypto = partialMock(EzpayCrypto::class);
            $ezpayCrypto->expects('encryptPostData')->with([
                'RespondType' => 'JSON',
                'Version' => '1.3',
                'TimeStamp' => Carbon::now()->timestamp,
                'MerchantOrderNo' => 'Order001',
                'InvoiceNumber' => '',
                'SearchType' => '1',
                'TotalAmt' => '1050',
                'RandomNum' => '',
                'DisplayFlag' => '1',
            ])->andReturn('encrypted_data');

            $response = EzpayInvoice::invoice()
                ->query()
                ->withOrder('Order001')
                ->withTotalAmount(1050)
                ->redirectToEZPay();

            expect($response)->toBeInstanceOf(Response::class)
                ->content()->toContain('https://cinv.ezpay.com.tw/Api/invoice_search')
                ->content()->toContain('name="MerchantID_" value="111335678"')
                ->content()->toContain('name="PostData_" value="encrypted_data"');
        });

        test('可以取得請求查詢發票的 formData 資料', function () {
            $ezpayCrypto = partialMock(EzpayCrypto::class);
            $ezpayCrypto->expects('encryptPostData')->with([
                'RespondType' => 'JSON',
                'Version' => '1.3',
                'TimeStamp' => Carbon::now()->timestamp,
                'MerchantOrderNo' => 'Order001',
                'InvoiceNumber' => '',
                'SearchType' => '1',
                'TotalAmt' => '1050',
                'RandomNum' => '',
                'DisplayFlag' => '1',
            ])->andReturn('encrypted_data');

            $requestData = EzpayInvoice::invoice()
                ->query()
                ->withOrder('Order001')
                ->withTotalAmount(1050)
                ->toRedirectRequestData();

            expect($requestData)->toBeArray()
                ->and($requestData['url'])->toBe('https://cinv.ezpay.com.tw/Api/invoice_search')
                ->and($requestData['formData'])->toBeArray()
                ->and($requestData['formData']['MerchantID_'])->toBe('111335678')
                ->and($requestData['formData']['PostData_'])->toBeString();
        });

        test('可以取得 ezPay 平台查詢發票的網址', function () {
            $ezpayCrypto = partialMock(EzpayCrypto::class);
            $ezpayCrypto->expects('encryptPostData')->with([
                'RespondType' => 'JSON',
                'Version' => '1.3',
                'TimeStamp' => Carbon::now()->timestamp,
                'MerchantOrderNo' => 'Order001',
                'InvoiceNumber' => '',
                'SearchType' => '1',
                'TotalAmt' => '1050',
                'RandomNum' => '',
                'DisplayFlag' => '2',
            ])->andReturn('encrypted_data');

            Http::fake([
                '*' => Http::response([
                    'Status' => 'SUCCESS',
                    'Message' => '查詢成功',
                    'Result' => 'https://cinv.ezpay.com.tw/Invoice_index/search_platform?PostData=xxxxxx',
                ], 200),
            ]);

            $invoiceQueryUrlResult = EzpayInvoice::invoice()
                ->query()
                ->withOrder('Order001')
                ->withTotalAmount(1050)
                ->getEZPayQueryUrl();

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api/invoice_search';
            });

            expect($invoiceQueryUrlResult)->toBeInstanceOf(InvoiceQueryUrlResult::class)
                ->and($invoiceQueryUrlResult->url())->toBe('https://cinv.ezpay.com.tw/Invoice_index/search_platform?PostData=xxxxxx');
        });
    });

    describe('發票作廢功能', function () {
        test('可以作廢已開立的發票', function () {
            $ezpayCrypto = partialMock(EzpayCrypto::class);
            $ezpayCrypto->expects('encryptPostData')->with([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'InvoiceNumber' => 'GG72002017',
                'InvalidReason' => '客戶取消訂單',
            ])->andReturn('encrypted_data');
            $ezpayCrypto->expects('verifyCheckCode')->andReturnNull();

            Http::fake([
                '*' => Http::response([
                    'Status' => 'SUCCESS',
                    'Message' => '電子發票作廢開立成功',
                    'Result' => json_encode([
                        'CheckCode' => '123456789',
                        'MerchantID' => '111335678',
                        'InvoiceNumber' => 'GG72002017',
                        'CreateTime' => '2025-01-01 00:00:00',
                    ]),
                ], 200),
            ]);

            $result = EzpayInvoice::invoice()
                ->query()
                ->find('GG72002017')
                ->because('客戶取消訂單')
                ->void();

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api/invoice_invalid';
            });

            expect($result)->toBeInstanceOf(Result::class)
                ->and($result->invoiceNumber())->toBe('GG72002017');
        });
    });

    describe('發票觸發功能', function () {
        test('可以觸發等待中的發票', function () {
            $ezpayCrypto = partialMock(EzpayCrypto::class);
            $ezpayCrypto->expects('encryptPostData')->with([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'InvoiceTransNo' => '25072516392250538',
                'MerchantOrderNo' => 'Order004',
                'TotalAmt' => '210',
            ])->andReturn('encrypted_data');
            $ezpayCrypto->expects('verifyCheckCode')->andReturnNull();

            Http::fake([
                '*' => Http::response([
                    'Status' => 'SUCCESS',
                    'Message' => '觸發開立發票成功',
                    'Result' => json_encode([
                        'CheckCode' => '123456789',
                        'MerchantID' => '111335678',
                        'MerchantOrderNo' => 'Order004',
                        'InvoiceNumber' => 'GG72002017',
                        'TotalAmt' => '210',
                        'InvoiceTransNo' => '25072516392250538',
                        'RandomNum' => '1234',
                        'CreateTime' => '2025-01-01 00:00:00',
                    ]),
                ], 200),
            ]);

            $result = EzpayInvoice::invoice()
                ->query()
                ->withInvoiceTransNo('25072516392250538')
                ->withOrder('Order004')
                ->withAmount(210)
                ->trigger();

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api/invoice_touch_issue';
            });

            expect($result)->toBeInstanceOf(Result::class)
                ->and($result->InvoiceNumber())->toBe('GG72002017');
        });
    });
});
