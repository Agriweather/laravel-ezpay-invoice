<?php

use Agriweather\EzpayInvoice\Facades\EzpayInvoice;
use Agriweather\EzpayInvoice\Results\Result;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

describe('境外電商折讓管理功能測試', function () {
    describe('境外電商折讓開立流程', function () {
        it('可以開立境外電商折讓', function () {
            Http::fake([
                '*' => Http::response([
                    'Status' => 'SUCCESS',
                    'Message' => '發票折讓開立成功',
                    'Result' => json_encode([
                        'CheckCode' => '123456789',
                        'AllowanceNo' => 'A250802013300379',
                        'InvoiceNumber' => 'CB00000022',
                        'MerchantID' => '111335678',
                        'MerchantOrderNo' => 'CBOrder001',
                        'AllowanceAmt' => '105.50',
                        'RemainAmt' => '0.00',
                    ]),
                ], 200),
            ]);

            $result = EzpayInvoice::crossBorder()
                ->allowance()
                ->create()
                ->withInvoice('CB00000016')
                ->withOrder('CBOrder001')
                ->withItem('退貨商品', quantity: 1, unit: 'EA', price: 105.50, amount: 105.50, tax: 0)
                ->withAmount(105.50)
                ->withNotification('customer@example.com')
                ->issue();

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api/crossBorderAllowanceIssue';
            });

            EzpayInvoice::assertSentPostData([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => time(),
                'InvoiceNo' => 'CB00000022',
                'MerchantOrderNo' => 'CBOrder001',
                'ItemName' => '退貨商品',
                'ItemCount' => '1',
                'ItemUnit' => 'EA',
                'ItemPrice' => '105.50',
                'ItemAmt' => '105.50',
                'ItemTaxAmt' => '0',
                'TotalAmt' => '105.50',
                'BuyerEmail' => 'customer@example.com',
                'Status' => '1',
            ]);

            expect($result)->toBeInstanceOf(Result::class)
                ->and($result->checkCode())->toBe('123456789')
                ->and($result->allowanceNo())->toBe('A250802013300379')
                ->and($result->orderNo())->toBe('CBOrder001')
                ->and($result->invoiceNumber())->toBe('CB00000022')
                ->and($result->allowanceAmount())->toBe(630)
                ->and($result->remainingAmount())->toBe(1050 - 630);
        });
    });

    describe('境外電商折讓觸發功能', function () {
        it('可以確認境外電商折讓', function () {
            Http::fake([
                '*' => Http::response([
                    'Status' => 'SUCCESS',
                    'Message' => '發票折讓觸發成功',
                    'Result' => json_encode([
                        'CheckCode' => '123456789',
                        'AllowanceNo' => 'A250802013300379',
                        'InvoiceNumber' => 'CB00000022',
                        'MerchantID' => '111335678',
                        'MerchantOrderNo' => 'CBOrder001',
                        'AllowanceAmt' => '105.50',
                        'RemainAmt' => '0.00',
                    ]),
                ], 200),
            ]);

            $result = EzpayInvoice::crossBorder()
                ->allowance()
                ->query()
                ->withAllowance('A250802013300379')
                ->withOrder('CBOrder001')
                ->withAmount(105.50)
                ->confirm();

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api/allowance_touch_issue';
            });

            EzpayInvoice::assertSentPostData([
                'RespondType' => 'JSON',
                'Version' => '1.3',
                'TimeStamp' => time(),
                'AllowanceStatus' => 'C',
                'AllowanceNo' => 'A250802013300379',
                'MerchantOrderNo' => 'CBOrder001',
                'TotalAmt' => '105.50',
            ]);

            expect($result)->toBeInstanceOf(Result::class)
                ->and($result->allowanceAmount)->toBe(105.50)
                ->and($result->remainingAmount)->toBe(0);
        });

        it('可以取消境外電商折讓', function () {
            Http::fake([
                '*' => Http::response([
                    'Status' => 'SUCCESS',
                    'Message' => '發票折讓刪除成功',
                    'Result' => json_encode([
                        'CheckCode' => '123456789',
                        'AllowanceNo' => 'A250802013300379',
                        'InvoiceNumber' => 'CB00000022',
                        'MerchantID' => '111335678',
                        'MerchantOrderNo' => 'CBOrder001',
                        'AllowanceAmt' => '0.00',
                        'RemainAmt' => '0.00',
                    ]),
                ], 200),
            ]);

            $result = EzpayInvoice::crossBorder()
                ->allowance()
                ->query()
                ->withAllowance('A250802013300379')
                ->withOrder('CBOrder001')
                ->withAmount(105.50)
                ->cancel();

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api/allowance_touch_issue';
            });

            EzpayInvoice::assertSentPostData([
                'RespondType' => 'JSON',
                'Version' => '1.3',
                'TimeStamp' => time(),
                'AllowanceStatus' => 'D',
                'AllowanceNo' => 'A250802013300379',
                'MerchantOrderNo' => 'CBOrder001',
                'TotalAmt' => '105.50',
            ]);

            expect($result)->toBeInstanceOf(Result::class)
                ->and($result->allowanceAmount)->toBe(0)
                ->and($result->remainingAmount)->toBe(0);
        });
    });

    describe('境外電商折讓作廢功能', function () {
        it('可以作廢已開立的境外電商折讓', function () {
            Http::fake([
                '*' => Http::response([
                    'Status' => 'SUCCESS',
                    'Message' => '作廢折讓成功',
                    'Result' => json_encode([
                        'MerchantID' => '111335678',
                        'AllowanceNo' => 'A250802013300379',
                        'CreateTime' => '2025-01-01 00:00:00',
                        'CheckCode' => '123456789',
                    ]),
                ], 200),
            ]);

            $result = EzpayInvoice::crossBorder()
                ->allowance()
                ->query()
                ->withAllowance('A250802013300379')
                ->because('作廢原因')
                ->void();

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api/allowanceInvalid';
            });

            EzpayInvoice::assertSentPostData([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => time(),
                'AllowanceNo' => 'A250802013300379',
                'InvalidReason' => '作廢原因',
            ]);

            expect($result)->toBeInstanceOf(Result::class)
                ->and($result['AllowanceNo'])->toBe('A250802013300379');
        });
    });
});
