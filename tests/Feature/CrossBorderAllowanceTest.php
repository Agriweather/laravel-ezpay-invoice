<?php

use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Facades\EzPayInvoice;
use Agriweather\EzPayInvoice\Options\Options;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\partialMock;

test('境外電商折讓開立 → 可以開立境外電商折讓', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptPostData')->andReturn('encrypted_data');

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

    $result = EzPayInvoice::crossBorder()
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

    expect($result)->toBeInstanceOf(CrossBorderAllowanceCreateResult::class)
        ->and($result->checkCode())->toBe('123456789')
        ->and($result->allowanceNo())->toBe('A250802013300379')
        ->and($result->orderNo())->toBe('CBOrder001')
        ->and($result->invoiceNumber())->toBe('CB00000022')
        ->and($result->allowanceAmount())->toBe(105.50)
        ->and($result->remainingAmount())->toBe(0.00);
});

test('境外電商折讓觸發 → 可以確認境外電商折讓', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptPostData')->andReturn('encrypted_data');

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

    $result = EzPayInvoice::crossBorder()
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

    expect($result)->toBeInstanceOf(CrossBorderAllowanceTriggerResult::class)
        ->and($result->allowanceAmount())->toBe(105.50)
        ->and($result->remainingAmount())->toBe(0);
});

test('境外電商折讓觸發 → 可以取消境外電商折讓', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptPostData')->andReturn('encrypted_data');

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

    $result = EzPayInvoice::crossBorder()
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

    expect($result)->toBeInstanceOf(CrossBorderAllowanceTriggerResult::class)
        ->and($result->allowanceAmount())->toBe(0.0)
        ->and($result->remainingAmount())->toBe(0.0);
});

test('境外電商折讓作廢 → 可以作廢已開立的境外電商折讓', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptPostData')->andReturn('encrypted_data');

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

    $result = EzPayInvoice::crossBorder()
        ->allowance()
        ->invalidateQuery()
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
        ->invalidate();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api/allowanceInvalid';
    });

    expect($result)->toBeInstanceOf(CrossBorderAllowanceInvalidateResult::class)
        ->and($result->allowanceNo())->toBe('A250802013300379');
});
