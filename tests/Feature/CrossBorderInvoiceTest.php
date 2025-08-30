<?php

use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Enums\Invoice\CurrencyType;
use Agriweather\EzPayInvoice\Facades\EzPayInvoice;
use Agriweather\EzPayInvoice\Options\Options;
use Agriweather\EzPayInvoice\Results\CrossBorderInvoice\CreateResult;
use Agriweather\EzPayInvoice\Results\CrossBorderInvoice\QueryResult;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\partialMock;

test('境外電商發票開立 → 可以成功開立境外電商發票', function () {
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
                'MerchantOrderNo' => 'CBOrder001',
                'InvoiceNumber' => 'CB00000016',
                'TotalAmt' => '105.50',
                'InvoiceTransNo' => '25080200501024251',
                'RandomNum' => '1234',
                'CreateTime' => '2025-01-01 00:00:00',
            ]),
        ], 200),
    ]);

    $result = EzPayInvoice::crossBorder()
        ->invoice()
        ->create()
        ->withOrder('CBOrder001')
        ->withCustomer('John Doe')
        ->withEmail('customer@example.com')
        ->withCurrency(CurrencyType::USD)
        ->withItem('國際商品', quantity: 1, unit: 'EA', price: 105.50, amount: 105.50)
        ->withAmount(100.00, 5.50, 105.50)
        ->withOriginalCurrencyAmount(100.00)
        ->withExchangeRate(30.5)
        ->transformOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'MerchantOrderNo' => 'CBOrder001',
                'Status' => '1',
                'BuyerName' => 'John Doe',
                'BuyerEmail' => 'customer@example.com',
                'Amt' => '100',
                'TaxAmt' => '5.5',
                'TotalAmt' => '105.5',
                'ItemName' => '國際商品',
                'ItemCount' => '1',
                'ItemUnit' => 'EA',
                'ItemPrice' => '105.5',
                'ItemAmt' => '105.5',
                'Currency' => 'USD',
                'OriginalCurrencyAmount' => '100',
                'ExchangeRate' => '30.5',
            ]);

            return $options;
        })
        ->issue();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api/crossBorderInvoiceIssue';
    });

    expect($result)->toBeInstanceOf(CreateResult::class)
        ->and($result->invoiceNumber())->toBe('CB00000016');
});

test('境外電商發票查詢 → 可以查詢境外電商發票', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptByAES')->andReturn('encrypted_data');
    $crypto->expects('verifyCheckCode')->andReturnNull();

    Http::fake([
        '*' => Http::response([
            'Status' => 'SUCCESS',
            'Message' => '查詢成功',
            'Result' => json_encode([
                'MerchantID' => '38219966',
                'InvoiceTransNo' => '25080201081479899',
                'MerchantOrderNo' => 'CBOrder1754068094',
                'InvoiceNumber' => 'CB00000020',
                'RandomNum' => '2697',
                'BuyerName' => 'John Doe',
                'BuyerAddress' => '',
                'BuyerEmail' => 'customer@example.com',
                'InvoiceType' => '07',
                'Amt' => '100.00',
                'TaxAmt' => '5.50',
                'TotalAmt' => '105.50',
                'CreateTime' => '2025-01-01 00:00:00',
                'ItemDetail' => json_encode([
                    [
                        'ItemName' => '國際商品',
                        'ItemCount' => '1',
                        'ItemWord' => 'EA',
                        'ItemPrice' => '105.5',
                        'ItemAmount' => '105.5',
                        'ItemTaxType' => '1',
                        'ItemNum' => '1',
                        'ItemRemark' => '',
                        'RelateNumber' => '',
                    ],
                ]),
                'InvoiceStatus' => '1',
                'UploadStatus' => '1',
                'OriginalCurrencyAmount' => '100.00',
                'ExchangeRate' => '30.50000',
                'Currency' => 'USD',
                'CheckCode' => '123456789',
            ]),
        ], 200),
    ]);

    $invoiceResult = EzPayInvoice::crossBorder()
        ->invoice()
        ->query()
        ->withInvoice('CB00000020')
        ->withRandomNumber('1234')
        ->transformOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'SearchType' => '0',
                'MerchantOrderNo' => '',
                'TotalAmt' => '0',
                'InvoiceNumber' => 'CB00000020',
                'RandomNum' => '1234',
            ]);

            return $options;
        })
        ->get();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api/crossBorderInvoiceSearch';
    });

    expect($invoiceResult)->toBeInstanceOf(QueryResult::class)
        ->and($invoiceResult->invoiceNumber())->toBe('CB00000020')
        ->and($invoiceResult->orderNo())->toBe('CBOrder1754068094')
        ->and($invoiceResult->totalAmount())->toBe(105.5)
        ->and($invoiceResult->buyerName())->toBe('John Doe')
        ->and($invoiceResult->buyerEmail())->toBe('customer@example.com');
});

test('境外電商發票觸發 → 可以觸發等待中的發票', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptByAES')->andReturn('encrypted_data');
    $crypto->expects('verifyCheckCode')->andReturnNull();

    Http::fake([
        '*' => Http::response([
            'Status' => 'SUCCESS',
            'Message' => '觸發開立發票成功',
            'Result' => json_encode([
                'CheckCode' => '123456789',
                'MerchantID' => '111335678',
                'MerchantOrderNo' => 'CBOrder001',
                'InvoiceNumber' => 'CB00000016',
                'TotalAmt' => '105.50',
                'InvoiceTransNo' => '25080200501024251',
                'RandomNum' => '1234',
                'CreateTime' => '2025-01-01 00:00:00',
            ]),
        ], 200),
    ]);

    $result = EzPayInvoice::crossBorder()
        ->invoice()
        ->triggerQuery()
        ->withInvoiceTransNo('25080200501024251')
        ->withOrder('CBOrder001')
        ->withTotalAmount(105.50)
        ->transformOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'InvoiceTransNo' => '25080200501024251',
                'MerchantOrderNo' => 'CBOrder001',
                'TotalAmt' => '105.50',
            ]);

            return $options;
        })
        ->trigger();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api/invoice_touch_issue';
    });

    expect($result)->toBeInstanceOf(TriggerResult::class)
        ->and($result->invoiceNumber())->toBe('CB00000016');
});

test('境外電商發票作廢 → 可以作廢已開立的發票', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptByAES')->andReturn('encrypted_data');

    Http::fake([
        '*' => Http::response([
            'Status' => 'SUCCESS',
            'Message' => '電子發票作廢開立成功',
            'Result' => json_encode([
                'CheckCode' => '123456789',
                'MerchantID' => '111335678',
                'InvoiceNumber' => 'CB00000016',
                'CreateTime' => '2025-01-01 00:00:00',
            ]),
        ], 200),
    ]);

    $result = EzPayInvoice::crossBorder()
        ->invoice()
        ->voidable()
        ->withInvoice('CB00000016')
        ->because('客戶取消訂單')
        ->transformOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'InvoiceNumber' => 'CB00000016',
                'InvalidReason' => '客戶取消訂單',
            ]);

            return $options;
        })
        ->invalidate();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api/invoice_invalid';
    });

    expect($result)->toBeInstanceOf(InvalidateResult::class)
        ->and($result->invoiceNumber())->toBe('CB00000016');
});
