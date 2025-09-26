<?php

use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Enums\Invoice\CurrencyType;
use Agriweather\EzPayInvoice\Facades\EzPayInvoice;
use Agriweather\EzPayInvoice\Options\CrossBorderInvoice\CreateOptions;
use Agriweather\EzPayInvoice\Options\Options;
use Agriweather\EzPayInvoice\Resources\CrossBorderInvoice;
use Agriweather\EzPayInvoice\Results\CrossBorderInvoice\CreateResult;
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
        ->withItem('國際商品', quantity: 1, unit: 'EA', price: 105.5, amount: 105.5)
        ->withAmount(100.0, 5.5, 105.5)
        ->withOriginalCurrencyAmount(100.0)
        ->withExchangeRate(30.5)
        ->onPreparedOptions(function (Options $options) {
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
        })
        ->issue();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api/crossBorderInvoiceIssue';
    });

    expect($result)->toBeInstanceOf(CreateResult::class)
        ->and($result->invoiceNumber())->toBe('CB00000016');
});

test('境外電商發票開立 → 模擬成功開立境外電商發票', function () {
    EzPayInvoice::fake([
        CreateResult::make([
            'Status' => 'SUCCESS',
            'Message' => '發票開立成功',
            'Result' => [
                'CheckCode' => '123456789',
                'MerchantID' => '111335678',
                'MerchantOrderNo' => 'CBOrder001',
                'InvoiceNumber' => 'CB00000016',
                'TotalAmt' => '105.50',
                'InvoiceTransNo' => '25080200501024251',
                'RandomNum' => '1234',
                'CreateTime' => '2025-01-01 00:00:00',
            ],
        ]),
    ]);

    $result = EzPayInvoice::crossBorder()
        ->invoice()
        ->create()
        ->withOrder('CBOrder001')
        ->withCustomer('John Doe')
        ->withEmail('customer@example.com')
        ->withCurrency(CurrencyType::USD)
        ->withItem('國際商品', quantity: 1, unit: 'EA', price: 105.5, amount: 105.5)
        ->withAmount(100.0, 5.5, 105.5)
        ->withOriginalCurrencyAmount(100.0)
        ->withExchangeRate(30.5)
        ->issue();

    EzPayInvoice::assertSent(CrossBorderInvoice::class, 'create', function (CreateOptions $options) {
        return $options->orderNo === 'CBOrder001'
            && $options->buyerName === 'John Doe'
            && $options->buyerEmail === 'customer@example.com'
            && $options->currency === CurrencyType::USD
            && $options->originalCurrencyAmount === 100.0
            && $options->exchangeRate === 30.5
            && $options->totalAmount === 105.5;
    });

    expect($result)->toBeInstanceOf(CreateResult::class)
        ->and($result->invoiceNumber())->toBe('CB00000016');
});
