<?php

use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Enums\Allowance\TriggerStatus;
use Agriweather\EzPayInvoice\Facades\EzPayInvoice;
use Agriweather\EzPayInvoice\Options\CrossBorderAllowance\CreateOptions;
use Agriweather\EzPayInvoice\Options\CrossBorderAllowance\InvalidateOptions;
use Agriweather\EzPayInvoice\Options\CrossBorderAllowance\TriggerOptions;
use Agriweather\EzPayInvoice\Options\Options;
use Agriweather\EzPayInvoice\Resources\CrossBorderAllowance;
use Agriweather\EzPayInvoice\Results\CrossBorderAllowance\CreateResult;
use Agriweather\EzPayInvoice\Results\CrossBorderAllowance\InvalidateResult;
use Agriweather\EzPayInvoice\Results\CrossBorderAllowance\TriggerResult;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\partialMock;

beforeEach(function () {
    $this->crypto = partialMock(Crypto::class);
    $this->crypto->expects('encryptByAES')->andReturn('encrypted_data');
});

test('境外電商折讓開立 → 可以開立境外電商折讓', function () {
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
        ->withInvoice('CB00000022')
        ->withOrder('CBOrder001')
        ->withItem('退貨商品', quantity: 1, unit: 'EA', price: 105.5, amount: 105.5)
        ->withTotalAmount(105.5)
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
                'ItemPrice' => '105.5',
                'ItemAmt' => '105.5',
                'ItemTaxAmt' => '0',
                'TotalAmt' => '105.5',
                'BuyerEmail' => 'customer@example.com',
                'Status' => '1',
            ]);

            return $options;
        })
        ->issue();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api/crossBorderAllowanceIssue';
    });

    expect($result)->toBeInstanceOf(CreateResult::class)
        ->and($result->checkCode())->toBe('123456789')
        ->and($result->allowanceNo())->toBe('A250802013300379')
        ->and($result->orderNo())->toBe('CBOrder001')
        ->and($result->invoiceNumber())->toBe('CB00000022')
        ->and($result->allowanceAmount())->toBe(105.5)
        ->and($result->remainingAmount())->toBe(0.0);
});

test('境外電商折讓觸發 → 可以確認境外電商折讓', function () {
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
        ->pending()
        ->withAllowance('A250802013300379')
        ->withOrder('CBOrder001')
        ->withTotalAmount(105.5)
        ->transformOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'RespondType' => 'JSON',
                'Version' => '1.3',
                'TimeStamp' => Carbon::now()->timestamp,
                'AllowanceStatus' => 'C',
                'AllowanceNo' => 'A250802013300379',
                'MerchantOrderNo' => 'CBOrder001',
                'TotalAmt' => '105.5',
            ]);

            return $options;
        })
        ->confirm();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api/allowance_touch_issue';
    });

    expect($result)->toBeInstanceOf(TriggerResult::class)
        ->and($result->allowanceAmount())->toBe(105.5)
        ->and($result->remainingAmount())->toBe(0.0);
});

test('境外電商折讓觸發 → 可以取消境外電商折讓', function () {
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
        ->pending()
        ->withAllowance('A250802013300379')
        ->withOrder('CBOrder001')
        ->withTotalAmount(105.5)
        ->transformOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'RespondType' => 'JSON',
                'Version' => '1.3',
                'TimeStamp' => Carbon::now()->timestamp,
                'AllowanceStatus' => 'D',
                'AllowanceNo' => 'A250802013300379',
                'MerchantOrderNo' => 'CBOrder001',
                'TotalAmt' => '105.5',
            ]);

            return $options;
        })
        ->cancel();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api/allowance_touch_issue';
    });

    expect($result)->toBeInstanceOf(TriggerResult::class)
        ->and($result->allowanceAmount())->toBe(0.0)
        ->and($result->remainingAmount())->toBe(0.0);
});

test('境外電商折讓作廢 → 可以作廢已開立的境外電商折讓', function () {
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
        ->voidable()
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

    expect($result)->toBeInstanceOf(InvalidateResult::class)
        ->and($result->allowanceNo())->toBe('A250802013300379');
});

test('境外電商折讓開立 → 測試斷言開立境外電商折讓', function () {
    EzPayInvoice::fake([
        CreateResult::make([
            'Status' => 'SUCCESS',
            'Message' => '發票折讓開立成功',
            'Result' => [
                'CheckCode' => '123456789',
                'AllowanceNo' => 'A250802013300379',
                'InvoiceNumber' => 'CB00000022',
                'MerchantID' => '111335678',
                'MerchantOrderNo' => 'CBOrder001',
                'AllowanceAmt' => '105.50',
                'RemainAmt' => '0.00',
            ],
        ]),
    ]);

    $result = EzPayInvoice::crossBorder()
        ->allowance()
        ->create()
        ->withInvoice('CB00000022')
        ->withOrder('CBOrder001')
        ->withItem('退貨商品', quantity: 1, unit: 'EA', price: 105.5, amount: 105.5)
        ->withTotalAmount(105.5)
        ->withNotification('customer@example.com')
        ->issue();

    EzPayInvoice::assertSent(CrossBorderAllowance::class, 'create', function (CreateOptions $options) {
        return $options->invoiceNo === 'CB00000022'
            && $options->orderNo === 'CBOrder001'
            && $options->totalAmount === 105.5;
    });

    expect($result)->toBeInstanceOf(CreateResult::class)
        ->and($result->allowanceNo())->toBe('A250802013300379');
});

test('境外電商折讓觸發 → 測試斷言確認境外電商折讓', function () {
    EzPayInvoice::fake([
        TriggerResult::make([
            'Status' => 'SUCCESS',
            'Message' => '發票折讓觸發成功',
            'Result' => [
                'CheckCode' => '123456789',
                'AllowanceNo' => 'A250802013300379',
                'InvoiceNumber' => 'CB00000022',
                'MerchantID' => '111335678',
                'MerchantOrderNo' => 'CBOrder001',
                'AllowanceAmt' => '105.50',
                'RemainAmt' => '0.00',
            ],
        ]),
    ]);

    $result = EzPayInvoice::crossBorder()
        ->allowance()
        ->pending()
        ->withAllowance('A250802013300379')
        ->withOrder('CBOrder001')
        ->withTotalAmount(105.5)
        ->confirm();

    EzPayInvoice::assertSent(CrossBorderAllowance::class, 'pending', function (TriggerOptions $options) {
        return $options->status === TriggerStatus::YES
            && $options->allowanceNo === 'A250802013300379';
    });

    expect($result)->toBeInstanceOf(TriggerResult::class);
});

test('境外電商折讓觸發 → 測試斷言取消境外電商折讓', function () {
    EzPayInvoice::fake([
        TriggerResult::make([
            'Status' => 'SUCCESS',
            'Message' => '發票折讓刪除成功',
            'Result' => [
                'CheckCode' => '123456789',
                'AllowanceNo' => 'A250802013300379',
                'InvoiceNumber' => 'CB00000022',
                'MerchantID' => '111335678',
                'MerchantOrderNo' => 'CBOrder001',
                'AllowanceAmt' => '0.00',
                'RemainAmt' => '0.00',
            ],
        ]),
    ]);

    $result = EzPayInvoice::crossBorder()
        ->allowance()
        ->pending()
        ->withAllowance('A250802013300379')
        ->withOrder('CBOrder001')
        ->withTotalAmount(105.5)
        ->cancel();

    EzPayInvoice::assertSent(CrossBorderAllowance::class, 'pending', function (TriggerOptions $options) {
        return $options->status === TriggerStatus::NO
            && $options->allowanceNo === 'A250802013300379';
    });

    expect($result)->toBeInstanceOf(TriggerResult::class);
});

test('境外電商折讓作廢 → 測試斷言作廢已開立的境外電商折讓', function () {
    EzPayInvoice::fake([
        InvalidateResult::make([
            'Status' => 'SUCCESS',
            'Message' => '作廢折讓成功',
            'Result' => [
                'MerchantID' => '111335678',
                'AllowanceNo' => 'A250802013300379',
                'CreateTime' => '2025-01-01 00:00:00',
                'CheckCode' => '123456789',
            ],
        ]),
    ]);

    $result = EzPayInvoice::crossBorder()
        ->allowance()
        ->voidable()
        ->withAllowance('A250802013300379')
        ->because('作廢原因')
        ->invalidate();

    EzPayInvoice::assertSent(CrossBorderAllowance::class, 'voidable', function (InvalidateOptions $options) {
        return $options->allowanceNo === 'A250802013300379'
            && $options->invalidReason === '作廢原因';
    });

    expect($result)->toBeInstanceOf(InvalidateResult::class)
        ->and($result->allowanceNo())->toBe('A250802013300379');
});
