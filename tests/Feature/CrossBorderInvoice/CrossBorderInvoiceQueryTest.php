<?php

use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Enums\Invoice\SearchType;
use Agriweather\EzPayInvoice\Facades\EzPayInvoice;
use Agriweather\EzPayInvoice\Options\CrossBorderInvoice\QueryOptions;
use Agriweather\EzPayInvoice\Options\Options;
use Agriweather\EzPayInvoice\Resources\CrossBorderInvoice;
use Agriweather\EzPayInvoice\Results\CrossBorderInvoice\QueryResult;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\partialMock;

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

test('境外電商發票查詢 → 模擬查詢境外電商發票', function () {
    EzPayInvoice::fake([
        QueryResult::make([
            'Status' => 'SUCCESS',
            'Message' => '查詢成功',
            'Result' => [
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
                'OriginalCurrencyAmount' => '100.00',
                'ExchangeRate' => '30.50000',
                'Currency' => 'USD',
                'CheckCode' => '123456789',
            ],
        ]),
    ]);

    $invoiceResult = EzPayInvoice::crossBorder()
        ->invoice()
        ->query()
        ->withInvoice('CB00000020')
        ->withRandomNumber('1234')
        ->get();

    EzPayInvoice::assertSent(CrossBorderInvoice::class, 'query', function (QueryOptions $options) {
        return $options->searchType === SearchType::BY_INVOICE_NUMBER
            && $options->invoiceNumber === 'CB00000020'
            && $options->randomNumber === '1234';
    });

    expect($invoiceResult)->toBeInstanceOf(QueryResult::class)
        ->and($invoiceResult->invoiceNumber())->toBe('CB00000020');
});
