<?php

use Agriweather\EzpayInvoice\Enums\CurrencyType;
use Agriweather\EzpayInvoice\Facades\EzpayInvoice;
use Agriweather\EzpayInvoice\Results\Result;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

describe('境外電商發票功能測試', function () {
    describe('境外電商發票開立', function () {
        it('可以成功開立境外電商發票', function () {
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

            $result = EzpayInvoice::crossBorder()
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
                ->issue();

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api/crossBorderInvoiceIssue';
            });

            EzpayInvoice::assertSentPostData([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => time(),
                'MerchantOrderNo' => 'CBOrder001',
                'Status' => '1',
                'BuyerName' => 'John Doe',
                'BuyerEmail' => 'customer@example.com',
                'Amt' => '100.00',
                'TaxAmt' => '5.50',
                'TotalAmt' => '105.50',
                'ItemName' => '國際商品',
                'ItemCount' => '1',
                'ItemUnit' => 'EA',
                'ItemPrice' => '105.50',
                'ItemAmt' => '105.50',
                'Currency' => 'USD',
                'OriginalCurrencyAmount' => '100.00',
                'ExchangeRate' => '30.5',
            ]);

            expect($result)->toBeInstanceOf(Result::class)
                ->and($result->invoiceNumber())->toBe('CB00000016');
        });
    });

    describe('境外電商發票查詢', function () {
        it('可以查詢境外電商發票', function () {
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

            $invoiceResult = EzpayInvoice::crossBorder()
                ->invoice()
                ->query()
                ->withInvoice('CBOrder001')
                ->withRandomNumber('1234')
                ->get();

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api/invoice_search';
            });

            EzpayInvoice::assertSentPostData([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => time(),
                'SearchType' => '0',
                'MerchantOrderNo' => '',
                'TotalAmt' => '',
                'InvoiceNumber' => 'CBOrder001',
                'RandomNum' => '1234',
            ]);

            expect($invoiceResult)->toBeInstanceOf(InvoiceResult::class)
                ->and($invoiceResult->invoiceNumber)->toBe('CBOrder001')
                ->and($invoiceResult->merchantOrderNo)->toBe('Order001')
                ->and($invoiceResult->totalAmount)->toBe(1050)
                ->and($invoiceResult->buyerName)->toBe('John Doe')
                ->and($invoiceResult->buyerEmail)->toBe('customer@example.com');
        });
    });
});
