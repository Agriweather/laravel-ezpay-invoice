<?php

use Agriweather\EzpayInvoice\Crypto\EzpayCrypto;
use Agriweather\EzpayInvoice\Facades\EzpayInvoice;
use Agriweather\EzpayInvoice\Options\Options;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\partialMock;

describe('境外電商折讓管理功能測試', function () {
    describe('境外電商折讓開立流程', function () {
        test('可以開立境外電商折讓', function () {
            $ezpayCrypto = partialMock(EzpayCrypto::class);
            $ezpayCrypto->expects('encryptPostData')->andReturn('encrypted_data');

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
                ->transformOptions(function (Options $options) {
                    expect($options->toArray()['PostData_'])->toBe([
                        'RespondType' => 'JSON',
                        'Version' => '1.0',
                        'TimeStamp' => Carbon::now()->timestamp,
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

                    return $options;
                })
                ->issue();

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api/crossBorderAllowanceIssue';
            });

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
        test('可以確認境外電商折讓', function () {
            $ezpayCrypto = partialMock(EzpayCrypto::class);
            $ezpayCrypto->expects('encryptPostData')->andReturn('encrypted_data');

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
                ->transformOptions(function (Options $options) {
                    expect($options->toArray()['PostData_'])->toBe([
                        'RespondType' => 'JSON',
                        'Version' => '1.3',
                        'TimeStamp' => Carbon::now()->timestamp,
                        'AllowanceStatus' => 'C',
                        'AllowanceNo' => 'A250802013300379',
                        'MerchantOrderNo' => 'CBOrder001',
                        'TotalAmt' => '105.50',
                    ]);

                    return $options;
                })
                ->confirm();

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api/allowance_touch_issue';
            });

            expect($result)->toBeInstanceOf(Result::class)
                ->and($result->allowanceAmount)->toBe(105.50)
                ->and($result->remainingAmount)->toBe(0);
        });

        test('可以取消境外電商折讓', function () {
            $ezpayCrypto = partialMock(EzpayCrypto::class);
            $ezpayCrypto->expects('encryptPostData')->andReturn('encrypted_data');

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
                ->transformOptions(function (Options $options) {
                    expect($options->toArray()['PostData_'])->toBe([
                        'RespondType' => 'JSON',
                        'Version' => '1.3',
                        'TimeStamp' => Carbon::now()->timestamp,
                        'AllowanceStatus' => 'D',
                        'AllowanceNo' => 'A250802013300379',
                        'MerchantOrderNo' => 'CBOrder001',
                        'TotalAmt' => '105.50',
                    ]);

                    return $options;
                })
                ->cancel();

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api/allowance_touch_issue';
            });

            expect($result)->toBeInstanceOf(Result::class)
                ->and($result->allowanceAmount)->toBe(0)
                ->and($result->remainingAmount)->toBe(0);
        });
    });

    describe('境外電商折讓作廢功能', function () {
        test('可以作廢已開立的境外電商折讓', function () {
            $ezpayCrypto = partialMock(EzpayCrypto::class);
            $ezpayCrypto->expects('encryptPostData')->andReturn('encrypted_data');

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
                ->transformOptions(function (Options $options) {
                    expect($options->toArray()['PostData_'])->toBe([
                        'RespondType' => 'JSON',
                        'Version' => '1.0',
                        'TimeStamp' => Carbon::now()->timestamp,
                        'AllowanceNo' => 'A250802013300379',
                        'InvalidReason' => '作廢原因',
                    ]);

                    return $options;
                })
                ->void();

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api/allowanceInvalid';
            });

            expect($result)->toBeInstanceOf(Result::class)
                ->and($result['AllowanceNo'])->toBe('A250802013300379');
        });
    });
});
