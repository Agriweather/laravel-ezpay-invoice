<?php

use Agriweather\EzpayInvoice\Enums\CarrierType;
use Agriweather\EzpayInvoice\Enums\TaxType;
use Agriweather\EzpayInvoice\Facades\EzpayInvoice;
use Agriweather\EzpayInvoice\Results\InvoiceResult;
use Agriweather\EzpayInvoice\Results\Result;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

describe('發票功能測試', function () {
    describe('發票開立流程', function () {
        it('可以成功開立 B2C 發票', function () {
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

            EzpayInvoice::assertSentPostData([
                'RespondType' => 'JSON',
                'Version' => '1.5',
                'TimeStamp' => time(),
                'MerchantOrderNo' => 'Order001',
                'Status' => '1',
                'Category' => 'B2C',
                'BuyerName' => 'John Doe',
                'BuyerEmail' => 'customer@example.com',
                'BuyerAddress' => '台北市信義區',
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

            expect($result)->toBeInstanceOf(Result::class)
                ->and($result->checkCode())->toBe('123456789')
                ->and($result->orderNo())->toBe('Order001')
                ->and($result->invoiceNumber())->toBe('GG72002017')
                ->and($result->totalAmount())->toBe(1050)
                ->and($result->randomNumber())->toBe('1234');
        });

        it('可以成功開立 B2B 發票', function () {
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
                ->withOrder('ORDER-002')
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

            EzpayInvoice::assertSentPostData([
                'RespondType' => 'JSON',
                'Version' => '1.5',
                'TimeStamp' => time(),
                'MerchantOrderNo' => 'Order002',
                'Status' => '1',
                'Category' => 'B2B',
                'BuyerName' => '測試公司有限公司',
                'BuyerUBN' => '12345678',
                'BuyerEmail' => 'business@company.com',
                'BuyerAddress' => '台北市信義區信義路五段7號',
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

            expect($result)->toBeInstanceOf(Result::class)
                ->and($result->checkCode())->toBe('123456789')
                ->and($result->orderNo())->toBe('Order002')
                ->and($result->invoiceNumber())->toBe('GG72002018')
                ->and($result->totalAmount())->toBe(1050)
                ->and($result->randomNumber())->toBe('1234');
        });

        it('可以開立載具發票', function () {
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
                ->withItem('載具商品', quantity: 1, unit: '個', price: 500, amount: 500)
                ->withTax(TaxType::TAXABLE, 5)
                ->withAmount(500, 25, 525)
                ->issue();

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api/invoice_issue';
            });

            EzpayInvoice::assertSentPostData([
                'RespondType' => 'JSON',
                'Version' => '1.5',
                'TimeStamp' => time(),
                'MerchantOrderNo' => 'Order003',
                'Status' => '1',
                'Category' => 'B2C',
                'BuyerName' => '載具客戶',
                'BuyerEmail' => 'customer@example.com',
                'BuyerAddress' => '台北市信義區',
                'CarrierType' => '0',
                'CarrierNum' => '/ABC.123',
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

            expect($result)->toBeInstanceOf(Result::class)
                ->and($result->orderNo())->toBe('Order003');
        });

        it('可以開立發票並等待觸發', function () {
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

            EzpayInvoice::assertSentPostData([
                'RespondType' => 'JSON',
                'Version' => '1.5',
                'TimeStamp' => time(),
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

            expect($result)->toBeInstanceOf(Result::class)
                ->and($result->orderNo())->toBe('Order004')
                ->and($result->invoiceNumber())->toBeNull()
                ->and($result->createTime())->toBeNull();
        });

        it('可以預約開立發票', function () {
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

            EzpayInvoice::assertSentPostData([
                'RespondType' => 'JSON',
                'Version' => '1.5',
                'TimeStamp' => time(),
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

            expect($result)->toBeInstanceOf(Result::class)
                ->and($result->orderNo())->toBe('Order005')
                ->and($result->invoiceNumber())->toBeNull()
                ->and($result->createTime())->toBeNull();
        });
    });

    describe('發票查詢功能', function () {
        it('可以透過發票號碼及隨機碼查詢發票', function () {
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

            EzpayInvoice::assertSentPostData([
                'RespondType' => 'JSON',
                'Version' => '1.3',
                'TimeStamp' => time(),
                'SearchType' => '0',
                'MerchantOrderNo' => '',
                'TotalAmt' => '',
                'InvoiceNumber' => 'GG72002017',
                'RandomNum' => '1234',
            ]);

            expect($invoiceResult)->toBeInstanceOf(InvoiceResult::class)
                ->and($invoiceResult->invoiceNumber)->toBe('GG72002017')
                ->and($invoiceResult->merchantOrderNo)->toBe('Order001')
                ->and($invoiceResult->totalAmount)->toBe(1050)
                ->and($invoiceResult->buyerName)->toBe('John Doe')
                ->and($invoiceResult->buyerEmail)->toBe('customer@example.com');
        });

        it('可以透過訂單編號及發票金額查詢發票', function () {
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
                ->withAmount(1050)
                ->get();

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api/invoice_search';
            });

            EzpayInvoice::assertSentPostData([
                'RespondType' => 'JSON',
                'Version' => '1.3',
                'TimeStamp' => time(),
                'SearchType' => '1',
                'MerchantOrderNo' => 'Order001',
                'TotalAmt' => '1050',
                'InvoiceNumber' => '',
                'RandomNum' => '',
            ]);

            expect($invoiceResult)->toBeInstanceOf(InvoiceResult::class)
                ->and($invoiceResult->invoiceNumber)->toBe('GG72002017')
                ->and($invoiceResult->merchantOrderNo)->toBe('Order001')
                ->and($invoiceResult->totalAmount)->toBe(1050)
                ->and($invoiceResult->buyerName)->toBe('John Doe')
                ->and($invoiceResult->buyerEmail)->toBe('customer@example.com');
        });

        it('可以跳轉到 ezPay 平台查詢發票', function () {
            /** @var \Illuminate\Http\Response */
            $response = EzpayInvoice::invoice()
                ->query()
                ->withOrder('Order001')
                ->withAmount(1050)
                ->redirectToEZPay();

            EzpayInvoice::assertPostDataHas('DisplayFlag', '1');

            expect($response)->toBeInstanceOf(Response::class)
                ->content()->toContain('https://cinv.ezpay.com.tw/Api/invoice_search')
                ->content()->toContain('name="MerchantID_" value="Order001"')
                ->content()->toContain('name="PostData_"');
        });

        it('可以取得請求查詢發票的 formData 資料', function () {
            /** @var array */
            $formData = EzpayInvoice::invoice()
                ->query()
                ->withOrder('Order001')
                ->withAmount(1050)
                ->toFormData();

            expect($formData)->toBeArray()
                ->and($formData['MerchantID_'])->toBe('Order001')
                ->and($formData['PostData_'])->toBeString();
        });
    });

    describe('發票作廢功能', function () {
        it('可以作廢已開立的發票', function () {
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

            EzpayInvoice::assertSentPostData([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => time(),
                'InvoiceNumber' => 'GG72002017',
                'InvalidReason' => '客戶取消訂單',
            ]);

            expect($result)->toBeInstanceOf(Result::class)
                ->and($result->invoiceNumber())->toBe('GG72002017');
        });
    });

    describe('發票觸發功能', function () {
        it('可以觸發等待中的發票', function () {
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

            EzpayInvoice::assertSentPostData([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => time(),
                'InvoiceTransNo' => '25072516392250538',
                'MerchantOrderNo' => 'Order004',
                'TotalAmt' => '210',
            ]);

            expect($result)->toBeInstanceOf(Result::class)
                ->and($result->InvoiceNumber())->toBe('GG72002017');
        });
    });
});
