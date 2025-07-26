<?php

use Agriweather\EzpayInvoice\Factory;

// use Agriweather\EzpayInvoice\Enums\CurrencyType;
// use Agriweather\EzpayInvoice\Enums\TaxType;
// use Agriweather\EzpayInvoice\Invoice;
// use Agriweather\EzpayInvoice\Results\Result;
// use Illuminate\Http\Client\Request;
// use Illuminate\Support\Facades\Http;

describe('境外電商發票功能測試', function () {
    beforeEach(function () {
        $this->factory = app(Factory::class);
    });

    describe('境外電商發票開立', function () {
        it('可以成功開立境外電商發票', function () {
            // Http::fake([
            //     '*' => Http::response([
            //         'Status' => 'SUCCESS',
            //         'Message' => '發票開立成功',
            //         'Result' => json_encode([
            //             'CheckCode' => '123456789',
            //             'MerchantID' => '111335678',
            //             'MerchantOrderNo' => 'Order001',
            //             'InvoiceNumber' => 'GG72002017',
            //             'TotalAmt' => 1050,
            //             'InvoiceTransNo' => '25072515224376654',
            //             'RandomNum' => '1234',
            //             'CreateTime' => '2025-01-01 00:00:00',
            //             'BarCode' => '11408GG720020179356',
            //             'QRcodeL' => 'GG7200201711407259356000003e80000041a0000000087612689JeS9LvMqldHvkH5bIDsJXw==:**********:1:1:1:國際商品:1:1000',
            //             'QRcodeR' => '**',
            //         ]),
            //     ], 200),
            // ]);

            // $result = $this->factory
            //     ->crossBorder()
            //     ->create()
            //     ->withOrder('CBOrder001')
            //     ->withCustomer('John Doe')
            //     ->withEmail('customer@example.com')
            //     ->withCurrency(CurrencyType::USD)
            //     ->withItem('國際商品', quantity: 1, unit: 'EA', price: 105.50, amount: 105.50)
            //     ->withAmount(100.00, 5.50, 105.50)
            //     ->withExchangeRate(30.5)
            //     ->issue();

            // Http::assertSent(function (Request $request) {
            //     return $request->url() == 'https://cinv.ezpay.com.tw/Api/crossBorderInvoiceIssue';
            // });

            // $this->factory->assertSentPostData([
            //     'RespondType' => 'JSON',
            //     'Version' => '1.0',
            //     'TimeStamp' => time(),
            //     'MerchantOrderNo' => 'CBOrder001',
            //     'Status' => '1',
            //     'BuyerName' => 'John Doe',
            //     'BuyerEmail' => 'customer@example.com',
            //     'Amt' => '100.00',
            //     'TaxAmt' => '5.50',
            //     'TotalAmt' => '105.50',
            //     'ItemName' => '國際商品',
            //     'ItemCount' => '1',
            //     'ItemUnit' => 'EA',
            //     'ItemPrice' => '105.50',
            //     'ItemAmt' => '105.50',
            //     'Currency' => 'USD',
            //     'ExchangeRate' => '30.5',
            // ]);

            // expect($result)->toBeInstanceOf(Result::class)
            //     ->and($result->invoiceNumber)->toBe('CB72002002');
        })->todo();

        it('可以開立多幣別境外電商發票', function () {
            // $this->mockCrossBorderInvoiceIssueSuccess();

            // $result = $this->factory
            //     ->crossBorder()
            //     ->create()
            //     ->withOrder('CBOrder002')
            //     ->withCustomer('歐洲客戶', '歐洲地址', 'europe@example.com')
            //     ->withCurrency(CurrencyType::EUR)
            //     ->withOriginalAmount(200.00)
            //     ->withExchangeRate(33.8)
            //     ->withAmount(6760.00, 0, 6760.00)
            //     ->withItem('商品A', quantity: 2, unit: 'EA', price: 50.00, amount: 100.00)
            //     ->withItem('商品B', quantity: 1, unit: 'EA', price: 100.00, amount: 100.00)
            //     ->issue();

            // expect($result)->toBeInstanceOf(Result::class);
        })->todo();
    });

    describe('境外電商發票查詢', function () {
        it('可以查詢境外電商發票', function () {
            // $this->mockCrossBorderInvoiceSearchSuccess([
            //     'Result' => [
            //         [
            //             'InvoiceNumber' => 'CB12345678',
            //             'MerchantOrderNo' => 'CBOrder001',
            //             'CurrencyCode' => 'USD',
            //             'TotalAmt' => '100',
            //             'CreateTime' => '2024-01-01 12:00:00',
            //         ],
            //     ],
            //     'TotalCount' => 1,
            // ]);

            // $result = $this->factory
            //     ->crossBorder()
            //     ->query()
            //     ->withOrder('CBOrder001')
            //     ->withAmount(3216.75)
            //     ->get();

            // expect($result)->toBeInstanceOf(Result::class)
            //     ->and($result['Result'])->toHaveCount(1)
            //     ->and($result['Result'][0]['CurrencyCode'])->toBe('USD');
        })->todo();

        it('可以透過幣別查詢境外電商發票', function () {
            // $this->mockCrossBorderInvoiceSearchSuccess([
            //     'Result' => [
            //         ['InvoiceNumber' => 'CB11111111', 'CurrencyCode' => 'EUR'],
            //         ['InvoiceNumber' => 'CB22222222', 'CurrencyCode' => 'EUR'],
            //     ],
            //     'TotalCount' => 2,
            // ]);

            // $result = $this->factory
            //     ->crossBorder()
            //     ->query()
            //     ->withCurrency(CurrencyType::EUR)
            //     ->get();

            // expect($result['Result'])->toHaveCount(2)
            //     ->and($result['Result'][0]['CurrencyCode'])->toBe('EUR');
        })->todo();
    });
});
