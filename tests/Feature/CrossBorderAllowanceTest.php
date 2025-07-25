<?php

use Agriweather\EzpayInvoice\Factory;

// use Agriweather\EzpayInvoice\Enums\TaxType;
// use Agriweather\EzpayInvoice\Result;
// use Illuminate\Http\Client\Request;
// use Illuminate\Support\Facades\Http;

describe('境外電商折讓管理功能測試', function () {
    beforeEach(function () {
        $this->factory = app(Factory::class);
    });

    describe('境外電商折讓開立流程', function () {
        it('可以開立境外電商折讓', function () {
            // Http::fake([
            //     '*' => Http::response([
            //         'Status' => 'SUCCESS',
            //         'Message' => '發票折讓開立成功',
            //         'Result' => json_encode([
            //             'CheckCode' => '123456789',
            //             'AllowanceNo' => 'A250725235346456',
            //             'InvoiceNumber' => 'GG72002018',
            //             'MerchantID' => '111335678',
            //             'MerchantOrderNo' => 'Order001',
            //             'AllowanceAmt' => 630,
            //             'RemainAmt' => 420,
            //         ]),
            //     ], 200),
            // ]);

            // /** @var \Agriweather\EzpayInvoice\Result */
            // $result = $this->factory
            //     ->allowance()
            //     ->create()
            //     ->withInvoice('GG72002018')
            //     ->withOrder('Order001')
            //     ->withItem('退貨商品', quantity: 2, unit: '個', price: 300, amount: 600, tax: 30)
            //     ->withAmount(630)
            //     ->withNotification('customer@example.com')
            //     ->issue();

            // Http::assertSent(function (Request $request) {
            //     return $request->url() == 'https://cinv.ezpay.com.tw/Api/allowance_issue';
            // });

            // $this->factory->assertSentPostData([
            //     'RespondType' => 'JSON',
            //     'Version' => '1.3',
            //     'TimeStamp' => time(),
            //     'InvoiceNo' => 'GG72002018',
            //     'MerchantOrderNo' => 'Order001',
            //     'ItemName' => '退貨商品',
            //     'ItemCount' => '2',
            //     'ItemUnit' => '個',
            //     'ItemPrice' => '300',
            //     'ItemAmt' => '600',
            //     'ItemTaxAmt' => '30',
            //     'TotalAmt' => '630',
            //     'Status' => '1',
            // ]);

            // expect($result)->toBeInstanceOf(Result::class)
            //     ->and($result->checkCode)->toBe('123456789')
            //     ->and($result->allowanceNo)->toBe('A250725235346456')
            //     ->and($result->merchantOrderNo)->toBe('Order001')
            //     ->and($result->invoiceNumber)->toBe('GG72002018')
            //     ->and($result->allowanceAmount)->toBe(630)
            //     ->and($result->remainingAmount)->toBe(1050 - 630);
        })->todo();

        it('可以開立多品項境外電商折讓', function () {
            // Http::fake([
            //     '*' => Http::response([
            //         'Status' => 'SUCCESS',
            //         'Message' => '發票折讓開立成功',
            //         'Result' => json_encode([
            //             'CheckCode' => '123456789',
            //             'AllowanceNo' => 'A250725235346456',
            //             'InvoiceNumber' => 'GG72002018',
            //             'MerchantID' => '111335678',
            //             'MerchantOrderNo' => 'Order001',
            //             'AllowanceAmt' => 157,
            //             'RemainAmt' => 0,
            //         ]),
            //     ], 200),
            // ]);

            // /** @var \Agriweather\EzpayInvoice\Result */
            // $result = $this->factory
            //     ->allowance()
            //     ->create()
            //     ->withInvoice('GG72002018')
            //     ->withOrder('Order001')
            //     ->withItem('商品A', quantity: 1, unit: '個', price: 100, amount: 100, tax: 5)
            //     ->withItem('商品B', quantity: 1, unit: '個', price: 50, amount: 50, tax: 2)
            //     ->withAmount(157)
            //     ->withNotification('company@example.com')
            //     ->issue();

            // Http::assertSent(function (Request $request) {
            //     return $request->url() == 'https://cinv.ezpay.com.tw/Api/allowance_issue';
            // });

            // $this->factory->assertSentPostData([
            //     'RespondType' => 'JSON',
            //     'Version' => '1.3',
            //     'TimeStamp' => time(),
            //     'InvoiceNo' => 'GG72002018',
            //     'MerchantOrderNo' => 'Order001',
            //     'ItemName' => '商品A|商品B',
            //     'ItemCount' => '1|1',
            //     'ItemUnit' => '個|個',
            //     'ItemPrice' => '100|50',
            //     'ItemAmt' => '100|50',
            //     'ItemTaxAmt' => '5|2',
            //     'TotalAmt' => '157',
            //     'Status' => '1',
            // ]);

            // expect($result)->toBeInstanceOf(Result::class);
        })->todo();

        it('可以開立非立即確認的境外電商折讓', function () {
            // Http::fake([
            //     '*' => Http::response([
            //         'Status' => 'SUCCESS',
            //         'Message' => '發票折讓開立成功',
            //         'Result' => json_encode([
            //             'CheckCode' => '123456789',
            //             'AllowanceNo' => 'A250726001830959',
            //             'InvoiceNumber' => 'GG72002018',
            //             'MerchantID' => '111335678',
            //             'MerchantOrderNo' => 'Order001',
            //             'AllowanceAmt' => 420,
            //             'RemainAmt' => 0,
            //         ]),
            //     ], 200),
            // ]);

            // /** @var \Agriweather\EzpayInvoice\Result */
            // $result = $this->factory
            //     ->allowance()
            //     ->create()
            //     ->withInvoice('GG72002018')
            //     ->withOrder('Order001')
            //     ->withItem('退貨商品', quantity: 2, unit: '個', price: 300, amount: 600, tax: 30)
            //     ->withAmount(630)
            //     ->pending()
            //     ->issue();

            // Http::assertSent(function (Request $request) {
            //     return $request->url() == 'https://cinv.ezpay.com.tw/Api/allowance_issue';
            // });

            // $this->factory->assertSentPostData([
            //     'RespondType' => 'JSON',
            //     'Version' => '1.3',
            //     'TimeStamp' => time(),
            //     'InvoiceNo' => 'GG72002018',
            //     'MerchantOrderNo' => 'Order001',
            //     'ItemName' => '退貨商品',
            //     'ItemCount' => '2',
            //     'ItemUnit' => '個',
            //     'ItemPrice' => '300',
            //     'ItemAmt' => '600',
            //     'ItemTaxAmt' => '30',
            //     'TotalAmt' => '630',
            //     'Status' => '1',
            // ]);

            // expect($result)->toBeInstanceOf(Result::class);
        })->todo();
    });

    describe('境外電商折讓觸發功能', function () {
        it('可以確認境外電商折讓', function () {
            // Http::fake([
            //     '*' => Http::response([
            //         'Status' => 'SUCCESS',
            //         'Message' => '發票折讓觸發成功',
            //         'Result' => json_encode([
            //             'CheckCode' => '123456789',
            //             'AllowanceNo' => 'A250726001830959',
            //             'InvoiceNumber' => 'GG72002018',
            //             'MerchantID' => '111335678',
            //             'MerchantOrderNo' => 'Order001',
            //             'AllowanceAmt' => '420',
            //             'RemainAmt' => '0',
            //         ]),
            //     ], 200),
            // ]);

            // /** @var \Agriweather\EzpayInvoice\Result */
            // $result = $this->factory
            //     ->allowance()
            //     ->query()
            //     ->withAllowance('A250726001830959')
            //     ->withOrder('Order001')
            //     ->withAmount(420)
            //     ->confirm();

            // Http::assertSent(function (Request $request) {
            //     return $request->url() == 'https://cinv.ezpay.com.tw/Api/allowance_touch_issue';
            // });

            // $this->factory->assertSentPostData([
            //     'RespondType' => 'JSON',
            //     'Version' => '1.0',
            //     'TimeStamp' => time(),
            //     'AllowanceStatus' => 'C',
            //     'AllowanceNo' => 'A250726001830959',
            //     'MerchantOrderNo' => 'Order001',
            //     'TotalAmt' => '420',
            // ]);

            // expect($result)->toBeInstanceOf(Result::class)
            //     ->and($result->allowanceAmount)->toBe(420)
            //     ->and($result->remainingAmount)->toBe(0);
        })->todo();

        it('可以取消境外電商折讓', function () {
            // Http::fake([
            //     '*' => Http::response([
            //         'Status' => 'SUCCESS',
            //         'Message' => '發票折讓刪除成功',
            //         'Result' => json_encode([
            //             'CheckCode' => '123456789',
            //             'AllowanceNo' => 'A250726001830959',
            //             'InvoiceNumber' => 'GG72002018',
            //             'MerchantID' => '111335678',
            //             'MerchantOrderNo' => 'Order001',
            //             'AllowanceAmt' => '0',
            //             'RemainAmt' => '0',
            //         ]),
            //     ], 200),
            // ]);

            // /** @var \Agriweather\EzpayInvoice\Result */
            // $result = $this->factory
            //     ->allowance()
            //     ->query()
            //     ->withAllowance('A250726001830959')
            //     ->withOrder('Order001')
            //     ->withAmount(420)
            //     ->cancel();

            // Http::assertSent(function (Request $request) {
            //     return $request->url() == 'https://cinv.ezpay.com.tw/Api/allowance_touch_issue';
            // });

            // $this->factory->assertSentPostData([
            //     'RespondType' => 'JSON',
            //     'Version' => '1.0',
            //     'TimeStamp' => time(),
            //     'AllowanceStatus' => 'D',
            //     'AllowanceNo' => 'A250726001830959',
            //     'MerchantOrderNo' => 'Order001',
            //     'TotalAmt' => '420',
            // ]);

            // expect($result)->toBeInstanceOf(Result::class)
            //     ->and($result->allowanceAmount)->toBe(0)
            //     ->and($result->remainingAmount)->toBe(0);
        })->todo();
    });

    describe('境外電商折讓作廢功能', function () {
        it('可以作廢已開立的境外電商折讓', function () {
            // Http::fake([
            //     '*' => Http::response([
            //         'Status' => 'SUCCESS',
            //         'Message' => '作廢折讓成功',
            //         'Result' => json_encode([
            //             'MerchantID' => '111335678',
            //             'AllowanceNo' => 'A250726001830959',
            //             'CreateTime' => '2025-01-01 00:00:00',
            //             'CheckCode' => '123456789',
            //         ]),
            //     ], 200),
            // ]);

            // $result = $this->factory
            //     ->allowance()
            //     ->query()
            //     ->withAllowance('AL24010001')
            //     ->because('作廢原因')
            //     ->void();

            // Http::assertSent(function (Request $request) {
            //     return $request->url() == 'https://cinv.ezpay.com.tw/Api/allowanceInvalid';
            // });

            // $this->factory->assertSentPostData([
            //     'RespondType' => 'JSON',
            //     'Version' => '1.0',
            //     'TimeStamp' => time(),
            //     'AllowanceNo' => 'A250726001830959',
            //     'InvalidReason' => '作廢原因',
            // ]);

            // expect($result)->toBeInstanceOf(Result::class)
            //     ->and($result['AllowanceNo'])->toBe('A250726001830959');
        })->todo();
    });
});
