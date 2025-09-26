<?php

use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Enums\Allowance\TriggerStatus;
use Agriweather\EzPayInvoice\Facades\EzPayInvoice;
use Agriweather\EzPayInvoice\Options\CrossBorderAllowance\TriggerOptions;
use Agriweather\EzPayInvoice\Options\Options;
use Agriweather\EzPayInvoice\Resources\CrossBorderAllowance;
use Agriweather\EzPayInvoice\Results\CrossBorderAllowance\TriggerResult;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\partialMock;

test('境外電商折讓觸發 → 可以確認境外電商折讓', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptByAES')->andReturn('encrypted_data');

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
        ->onPreparedOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'RespondType' => 'JSON',
                'Version' => '1.3',
                'TimeStamp' => Carbon::now()->timestamp,
                'AllowanceStatus' => 'C',
                'AllowanceNo' => 'A250802013300379',
                'MerchantOrderNo' => 'CBOrder001',
                'TotalAmt' => '105.5',
            ]);
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
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptByAES')->andReturn('encrypted_data');

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
        ->onPreparedOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'RespondType' => 'JSON',
                'Version' => '1.3',
                'TimeStamp' => Carbon::now()->timestamp,
                'AllowanceStatus' => 'D',
                'AllowanceNo' => 'A250802013300379',
                'MerchantOrderNo' => 'CBOrder001',
                'TotalAmt' => '105.5',
            ]);
        })
        ->cancel();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api/allowance_touch_issue';
    });

    expect($result)->toBeInstanceOf(TriggerResult::class)
        ->and($result->allowanceAmount())->toBe(0.0)
        ->and($result->remainingAmount())->toBe(0.0);
});

test('境外電商折讓觸發 → 模擬確認境外電商折讓', function () {
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

test('境外電商折讓觸發 → 模擬取消境外電商折讓', function () {
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
