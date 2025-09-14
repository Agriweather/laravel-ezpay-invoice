<?php

use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Enums\Invoice\DisplayFlag;
use Agriweather\EzPayInvoice\Enums\Invoice\SearchType;
use Agriweather\EzPayInvoice\Facades\EzPayInvoice;
use Agriweather\EzPayInvoice\Options\Invoice\QueryOptions;
use Agriweather\EzPayInvoice\Options\Options;
use Agriweather\EzPayInvoice\Resources\Invoice;
use Agriweather\EzPayInvoice\Results\Invoice\QueryResult;
use Agriweather\EzPayInvoice\Results\Invoice\UrlQueryResult;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\partialMock;

test('發票查詢 → 可以透過發票號碼及隨機碼查詢發票', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptByAES')->andReturn('encrypted_data');
    $crypto->expects('verifyCheckCode')->andReturnNull();

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

    $invoiceResult = EzPayInvoice::invoice()
        ->query()
        ->withInvoice('GG72002017')
        ->withRandomNumber('1234')
        ->transformOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'RespondType' => 'JSON',
                'Version' => '1.3',
                'TimeStamp' => Carbon::now()->timestamp,
                'SearchType' => '0',
                'MerchantOrderNo' => '',
                'TotalAmt' => '0',
                'InvoiceNumber' => 'GG72002017',
                'RandomNum' => '1234',
            ]);

            return $options;
        })
        ->get();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api/invoice_search';
    });

    expect($invoiceResult)->toBeInstanceOf(QueryResult::class)
        ->and($invoiceResult->invoiceNumber())->toBe('GG72002017')
        ->and($invoiceResult->orderNo())->toBe('Order001')
        ->and($invoiceResult->totalAmount())->toBe(1050)
        ->and($invoiceResult->buyerName())->toBe('John Doe')
        ->and($invoiceResult->buyerEmail())->toBe('customer@example.com');
});

test('發票查詢 → 可以透過訂單編號及發票金額查詢發票', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptByAES')->andReturn('encrypted_data');
    $crypto->expects('verifyCheckCode')->andReturnNull();

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

    $invoiceResult = EzPayInvoice::invoice()
        ->query()
        ->withOrder('Order001')
        ->withTotalAmount(1050)
        ->transformOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'RespondType' => 'JSON',
                'Version' => '1.3',
                'TimeStamp' => Carbon::now()->timestamp,
                'SearchType' => '1',
                'MerchantOrderNo' => 'Order001',
                'TotalAmt' => '1050',
                'InvoiceNumber' => '',
                'RandomNum' => '',
            ]);

            return $options;
        })
        ->get();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api/invoice_search';
    });

    expect($invoiceResult)->toBeInstanceOf(QueryResult::class)
        ->and($invoiceResult->invoiceNumber())->toBe('GG72002017')
        ->and($invoiceResult->orderNo())->toBe('Order001')
        ->and($invoiceResult->totalAmount())->toBe(1050)
        ->and($invoiceResult->buyerName())->toBe('John Doe')
        ->and($invoiceResult->buyerEmail())->toBe('customer@example.com');
});

test('發票查詢 → 可以跳轉到 ezPay 平台查詢發票', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptByAES')->andReturn('encrypted_data');

    $response = EzPayInvoice::invoice()
        ->query()
        ->withOrder('Order001')
        ->withTotalAmount(1050)
        ->redirectToEzPay();

    expect($response)->toBeInstanceOf(Response::class)
        ->content()->toContain('action="https://cinv.ezpay.com.tw/Api/invoice_search"')
        ->content()->toContain('name="MerchantID_" value="111335678"')
        ->content()->toContain('name="PostData_" value="encrypted_data"');
});

test('發票查詢 → 可以取得請求查詢發票的 formData 資料', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptByAES')->andReturn('encrypted_data');

    $requestData = EzPayInvoice::invoice()
        ->query()
        ->withOrder('Order001')
        ->withTotalAmount(1050)
        ->transformOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'RespondType' => 'JSON',
                'Version' => '1.3',
                'TimeStamp' => Carbon::now()->timestamp,
                'SearchType' => '1',
                'MerchantOrderNo' => 'Order001',
                'TotalAmt' => '1050',
                'InvoiceNumber' => '',
                'RandomNum' => '',
                'DisplayFlag' => '1',
            ]);

            return $options;
        })
        ->toRedirectRequestData();

    expect($requestData)->toBeArray()
        ->and($requestData['url'])->toBe('https://cinv.ezpay.com.tw/Api/invoice_search')
        ->and($requestData['formData'])->toBeArray()
        ->and($requestData['formData']['MerchantID_'])->toBe('111335678')
        ->and($requestData['formData']['PostData_'])->toBeString();
});

test('發票查詢 → 可以取得 ezPay 平台查詢發票的網址', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptByAES')->andReturn('encrypted_data');

    Http::fake([
        '*' => Http::response([
            'Status' => 'SUCCESS',
            'Message' => '查詢成功',
            'Result' => 'https://cinv.ezpay.com.tw/Invoice_index/search_platform?PostData=xxxxxx',
        ], 200),
    ]);

    $ezpaySearchUrl = EzPayInvoice::invoice()
        ->query()
        ->withOrder('Order001')
        ->withTotalAmount(1050)
        ->getEzPaySearchUrl();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api/invoice_search';
    });

    expect($ezpaySearchUrl)->toBe('https://cinv.ezpay.com.tw/Invoice_index/search_platform?PostData=xxxxxx');
});

test('發票查詢 → 模擬透過發票號碼及隨機碼查詢發票', function () {
    EzPayInvoice::fake([
        QueryResult::make([
            'Status' => 'SUCCESS',
            'Message' => '查詢成功',
            'Result' => [
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
                'ItemDetail' => [
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
                ],
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
            ],
        ]),
    ]);

    $invoiceResult = EzPayInvoice::invoice()
        ->query()
        ->withInvoice('GG72002017')
        ->withRandomNumber('1234')
        ->get();

    EzPayInvoice::assertSent(Invoice::class, 'query', function (QueryOptions $options) {
        return $options->searchType === SearchType::BY_INVOICE_NUMBER
            && $options->invoiceNumber === 'GG72002017'
            && $options->randomNumber === '1234'
            && $options->orderNo === ''
            && $options->totalAmount === 0;
    });

    expect($invoiceResult)->toBeInstanceOf(QueryResult::class)
        ->and($invoiceResult->invoiceNumber())->toBe('GG72002017')
        ->and($invoiceResult->orderNo())->toBe('Order001')
        ->and($invoiceResult->totalAmount())->toBe(1050)
        ->and($invoiceResult->buyerName())->toBe('John Doe')
        ->and($invoiceResult->buyerEmail())->toBe('customer@example.com');
});

test('發票查詢 → 模擬透過訂單編號及發票金額查詢發票', function () {
    EzPayInvoice::fake([
        QueryResult::make([
            'Status' => 'SUCCESS',
            'Message' => '查詢成功',
            'Result' => [
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
                'ItemDetail' => [
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
                ],
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
            ],
        ]),
    ]);

    $invoiceResult = EzPayInvoice::invoice()
        ->query()
        ->withOrder('Order001')
        ->withTotalAmount(1050)
        ->get();

    EzPayInvoice::assertSent(Invoice::class, 'query', function (QueryOptions $options) {
        return $options->searchType === SearchType::BY_ORDER_NUMBER
            && $options->orderNo === 'Order001'
            && $options->totalAmount === 1050
            && $options->invoiceNumber === ''
            && $options->randomNumber === '';
    });

    expect($invoiceResult)->toBeInstanceOf(QueryResult::class)
        ->and($invoiceResult->invoiceNumber())->toBe('GG72002017')
        ->and($invoiceResult->orderNo())->toBe('Order001')
        ->and($invoiceResult->totalAmount())->toBe(1050)
        ->and($invoiceResult->buyerName())->toBe('John Doe')
        ->and($invoiceResult->buyerEmail())->toBe('customer@example.com');
});

test('發票查詢 → 模擬取得 ezPay 平台查詢發票的網址', function () {
    EzPayInvoice::fake([
        UrlQueryResult::make([
            'Status' => 'SUCCESS',
            'Message' => '查詢成功',
            'Result' => 'https://cinv.ezpay.com.tw/Invoice_index/search_platform?PostData=xxxxxx',
        ]),
    ]);

    $ezpaySearchUrl = EzPayInvoice::invoice()
        ->query()
        ->withOrder('Order001')
        ->withTotalAmount(1050)
        ->getEzPaySearchUrl();

    EzPayInvoice::assertSent(Invoice::class, 'query', function (QueryOptions $options) {
        return $options->orderNo === 'Order001'
            && $options->totalAmount === 1050
            && $options->displayFlag === DisplayFlag::RETURN_URL;
    });

    expect($ezpaySearchUrl)->toBe('https://cinv.ezpay.com.tw/Invoice_index/search_platform?PostData=xxxxxx');
});
