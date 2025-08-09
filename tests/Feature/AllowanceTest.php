<?php

use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Facades\EzPayInvoice;
use Agriweather\EzPayInvoice\Options\Options;
use Agriweather\EzPayInvoice\Results\AllowanceCreateResult;
use Agriweather\EzPayInvoice\Results\AllowanceInvalidateResult;
use Agriweather\EzPayInvoice\Results\AllowanceTriggerResult;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\partialMock;

describe('折讓管理功能測試', function () {
    describe('折讓開立流程', function () {
        test('可以成功開立折讓', function () {
            $crypto = partialMock(Crypto::class);
            $crypto->expects('encryptPostData')->andReturn('encrypted_data');

            Http::fake([
                '*' => Http::response([
                    'Status' => 'SUCCESS',
                    'Message' => '發票折讓開立成功',
                    'Result' => json_encode([
                        'CheckCode' => '123456789',
                        'AllowanceNo' => 'A250725235346456',
                        'InvoiceNumber' => 'GG72002018',
                        'MerchantID' => '111335678',
                        'MerchantOrderNo' => 'Order001',
                        'AllowanceAmt' => 630,
                        'RemainAmt' => 420,
                    ]),
                ], 200),
            ]);

            $result = EzPayInvoice::allowance()
                ->create()
                ->withInvoice('GG72002018')
                ->withOrder('Order001')
                ->withItem('退貨商品', quantity: 2, unit: '個', price: 300, amount: 600, taxAmount: 30)
                ->withTotalAmount(630)
                ->withNotification('customer@example.com')
                ->transformOptions(function (Options $options) {
                    expect($options->toArray()['PostData_'])->toBe([
                        'RespondType' => 'JSON',
                        'Version' => '1.3',
                        'TimeStamp' => Carbon::now()->timestamp,
                        'InvoiceNo' => 'GG72002018',
                        'MerchantOrderNo' => 'Order001',
                        'ItemName' => '退貨商品',
                        'ItemCount' => '2',
                        'ItemUnit' => '個',
                        'ItemPrice' => '300',
                        'ItemAmt' => '600',
                        'ItemTaxAmt' => '30',
                        'TotalAmt' => '630',
                        'BuyerEmail' => 'customer@example.com',
                        'Status' => '1',
                    ]);

                    return $options;
                })
                ->issue();

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api/allowance_issue';
            });

            expect($result)->toBeInstanceOf(AllowanceCreateResult::class)
                ->and($result->checkCode())->toBe('123456789')
                ->and($result->allowanceNo())->toBe('A250725235346456')
                ->and($result->orderNo())->toBe('Order001')
                ->and($result->invoiceNumber())->toBe('GG72002018')
                ->and($result->allowanceAmount())->toBe(630)
                ->and($result->remainingAmount())->toBe(1050 - 630);
        });

        test('可以開立多品項折讓', function () {
            $crypto = partialMock(Crypto::class);
            $crypto->expects('encryptPostData')->andReturn('encrypted_data');

            Http::fake([
                '*' => Http::response([
                    'Status' => 'SUCCESS',
                    'Message' => '發票折讓開立成功',
                    'Result' => json_encode([
                        'CheckCode' => '123456789',
                        'AllowanceNo' => 'A250725235346456',
                        'InvoiceNumber' => 'GG72002018',
                        'MerchantID' => '111335678',
                        'MerchantOrderNo' => 'Order001',
                        'AllowanceAmt' => 157,
                        'RemainAmt' => 0,
                    ]),
                ], 200),
            ]);

            $result = EzPayInvoice::allowance()
                ->create()
                ->withInvoice('GG72002018')
                ->withOrder('Order001')
                ->withItem('商品A', quantity: 1, unit: '個', price: 100, amount: 100, taxAmount: 5)
                ->withItem('商品B', quantity: 1, unit: '個', price: 50, amount: 50, taxAmount: 2)
                ->withTotalAmount(157)
                ->withNotification('customer@example.com')
                ->transformOptions(function (Options $options) {
                    expect($options->toArray()['PostData_'])->toBe([
                        'RespondType' => 'JSON',
                        'Version' => '1.3',
                        'TimeStamp' => Carbon::now()->timestamp,
                        'InvoiceNo' => 'GG72002018',
                        'MerchantOrderNo' => 'Order001',
                        'ItemName' => '商品A|商品B',
                        'ItemCount' => '1|1',
                        'ItemUnit' => '個|個',
                        'ItemPrice' => '100|50',
                        'ItemAmt' => '100|50',
                        'ItemTaxAmt' => '5|2',
                        'TotalAmt' => '157',
                        'BuyerEmail' => 'customer@example.com',
                        'Status' => '1',
                    ]);

                    return $options;
                })
                ->issue();

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api/allowance_issue';
            });

            expect($result)->toBeInstanceOf(AllowanceCreateResult::class);
        });

        test('可以開立非立即確認的折讓', function () {
            $crypto = partialMock(Crypto::class);
            $crypto->expects('encryptPostData')->andReturn('encrypted_data');

            Http::fake([
                '*' => Http::response([
                    'Status' => 'SUCCESS',
                    'Message' => '發票折讓開立成功',
                    'Result' => json_encode([
                        'CheckCode' => '123456789',
                        'AllowanceNo' => 'A250726001830959',
                        'InvoiceNumber' => 'GG72002018',
                        'MerchantID' => '111335678',
                        'MerchantOrderNo' => 'Order001',
                        'AllowanceAmt' => 420,
                        'RemainAmt' => 0,
                    ]),
                ], 200),
            ]);

            $result = EzPayInvoice::allowance()
                ->create()
                ->withInvoice('GG72002018')
                ->withOrder('Order001')
                ->withItem('退貨商品', quantity: 2, unit: '個', price: 300, amount: 600, taxAmount: 30)
                ->withTotalAmount(630)
                ->transformOptions(function (Options $options) {
                    expect($options->toArray()['PostData_'])->toBe([
                        'RespondType' => 'JSON',
                        'Version' => '1.3',
                        'TimeStamp' => Carbon::now()->timestamp,
                        'InvoiceNo' => 'GG72002018',
                        'MerchantOrderNo' => 'Order001',
                        'ItemName' => '退貨商品',
                        'ItemCount' => '2',
                        'ItemUnit' => '個',
                        'ItemPrice' => '300',
                        'ItemAmt' => '600',
                        'ItemTaxAmt' => '30',
                        'TotalAmt' => '630',
                        'Status' => '0',
                    ]);

                    return $options;
                })
                ->delayCheck()
                ->issue();

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api/allowance_issue';
            });

            expect($result)->toBeInstanceOf(AllowanceCreateResult::class);
        });
    });

    describe('折讓觸發功能', function () {
        test('可以確認折讓', function () {
            $crypto = partialMock(Crypto::class);
            $crypto->expects('encryptPostData')->andReturn('encrypted_data');

            Http::fake([
                '*' => Http::response([
                    'Status' => 'SUCCESS',
                    'Message' => '發票折讓觸發成功',
                    'Result' => json_encode([
                        'CheckCode' => '123456789',
                        'AllowanceNo' => 'A250726001830959',
                        'InvoiceNumber' => 'GG72002018',
                        'MerchantID' => '111335678',
                        'MerchantOrderNo' => 'Order001',
                        'AllowanceAmt' => '420',
                        'RemainAmt' => '0',
                    ]),
                ], 200),
            ]);

            $result = EzPayInvoice::allowance()
                ->triggerQuery()
                ->withAllowance('A250726001830959')
                ->withOrder('Order001')
                ->withTotalAmount(420)
                ->transformOptions(function (Options $options) {
                    expect($options->toArray()['PostData_'])->toBe([
                        'RespondType' => 'JSON',
                        'Version' => '1.0',
                        'TimeStamp' => Carbon::now()->timestamp,
                        'AllowanceStatus' => 'C',
                        'AllowanceNo' => 'A250726001830959',
                        'MerchantOrderNo' => 'Order001',
                        'TotalAmt' => '420',
                    ]);

                    return $options;
                })
                ->confirm();

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api/allowance_touch_issue';
            });

            expect($result)->toBeInstanceOf(AllowanceTriggerResult::class)
                ->and($result->allowanceAmount())->toBe(420)
                ->and($result->remainingAmount())->toBe(0);
        });

        test('可以取消折讓', function () {
            $crypto = partialMock(Crypto::class);
            $crypto->expects('encryptPostData')->andReturn('encrypted_data');

            Http::fake([
                '*' => Http::response([
                    'Status' => 'SUCCESS',
                    'Message' => '發票折讓刪除成功',
                    'Result' => json_encode([
                        'CheckCode' => '123456789',
                        'AllowanceNo' => 'A250726001830959',
                        'InvoiceNumber' => 'GG72002018',
                        'MerchantID' => '111335678',
                        'MerchantOrderNo' => 'Order001',
                        'AllowanceAmt' => '0',
                        'RemainAmt' => '0',
                    ]),
                ], 200),
            ]);

            $result = EzPayInvoice::allowance()
                ->triggerQuery()
                ->withAllowance('A250726001830959')
                ->withOrder('Order001')
                ->withTotalAmount(420)
                ->transformOptions(function (Options $options) {
                    expect($options->toArray()['PostData_'])->toBe([
                        'RespondType' => 'JSON',
                        'Version' => '1.0',
                        'TimeStamp' => Carbon::now()->timestamp,
                        'AllowanceStatus' => 'D',
                        'AllowanceNo' => 'A250726001830959',
                        'MerchantOrderNo' => 'Order001',
                        'TotalAmt' => '420',
                    ]);

                    return $options;
                })
                ->cancel();

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api/allowance_touch_issue';
            });

            expect($result)->toBeInstanceOf(AllowanceTriggerResult::class)
                ->and($result->allowanceAmount())->toBe(0)
                ->and($result->remainingAmount())->toBe(0);
        });
    });

    describe('折讓作廢功能', function () {
        test('可以作廢已開立的折讓', function () {
            $crypto = partialMock(Crypto::class);
            $crypto->expects('encryptPostData')->andReturn('encrypted_data');

            Http::fake([
                '*' => Http::response([
                    'Status' => 'SUCCESS',
                    'Message' => '作廢折讓成功',
                    'Result' => json_encode([
                        'MerchantID' => '111335678',
                        'AllowanceNo' => 'A250726001830959',
                        'CreateTime' => '2025-01-01 00:00:00',
                        'CheckCode' => '123456789',
                    ]),
                ], 200),
            ]);

            $result = EzPayInvoice::allowance()
                ->invalidateQuery()
                ->withAllowance('A250726001830959')
                ->because('作廢原因')
                ->transformOptions(function (Options $options) {
                    expect($options->toArray()['PostData_'])->toBe([
                        'RespondType' => 'JSON',
                        'Version' => '1.0',
                        'TimeStamp' => Carbon::now()->timestamp,
                        'AllowanceNo' => 'A250726001830959',
                        'InvalidReason' => '作廢原因',
                    ]);

                    return $options;
                })
                ->invalidate();

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api/allowanceInvalid';
            });

            expect($result)->toBeInstanceOf(AllowanceInvalidateResult::class)
                ->and($result->allowanceNo())->toBe('A250726001830959');
        });
    });
});
