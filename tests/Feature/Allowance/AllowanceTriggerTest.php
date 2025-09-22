<?php

use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Enums\Allowance\TriggerStatus;
use Agriweather\EzPayInvoice\Facades\EzPayInvoice;
use Agriweather\EzPayInvoice\Options\Allowance\TriggerOptions;
use Agriweather\EzPayInvoice\Options\Options;
use Agriweather\EzPayInvoice\Resources\Allowance;
use Agriweather\EzPayInvoice\Results\Allowance\TriggerResult;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\partialMock;

test('折讓觸發 → 可以確認折讓', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptByAES')->andReturn('encrypted_data');

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
        ->pending()
        ->withAllowance('A250726001830959')
        ->withOrder('Order001')
        ->withTotalAmount(420)
        ->onPrepareOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'AllowanceStatus' => 'C',
                'AllowanceNo' => 'A250726001830959',
                'MerchantOrderNo' => 'Order001',
                'TotalAmt' => '420',
            ]);
        })
        ->confirm();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api/allowance_touch_issue';
    });

    expect($result)->toBeInstanceOf(TriggerResult::class)
        ->and($result->allowanceAmount())->toBe(420)
        ->and($result->remainingAmount())->toBe(0);
});

test('折讓觸發 → 可以取消折讓', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptByAES')->andReturn('encrypted_data');

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
        ->pending()
        ->withAllowance('A250726001830959')
        ->withOrder('Order001')
        ->withTotalAmount(420)
        ->onPrepareOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'AllowanceStatus' => 'D',
                'AllowanceNo' => 'A250726001830959',
                'MerchantOrderNo' => 'Order001',
                'TotalAmt' => '420',
            ]);
        })
        ->cancel();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api/allowance_touch_issue';
    });

    expect($result)->toBeInstanceOf(TriggerResult::class)
        ->and($result->allowanceAmount())->toBe(0)
        ->and($result->remainingAmount())->toBe(0);
});

test('折讓觸發 → 模擬確認折讓', function () {
    EzPayInvoice::fake([
        TriggerResult::make([
            'Status' => 'SUCCESS',
            'Message' => '發票折讓觸發成功',
            'Result' => [
                'CheckCode' => '123456789',
                'AllowanceNo' => 'A250726001830959',
                'InvoiceNumber' => 'GG72002018',
                'MerchantID' => '111335678',
                'MerchantOrderNo' => 'Order001',
                'AllowanceAmt' => '420',
                'RemainAmt' => '0',
            ],
        ]),
    ]);

    $result = EzPayInvoice::allowance()
        ->pending()
        ->withAllowance('A250726001830959')
        ->withOrder('Order001')
        ->withTotalAmount(420)
        ->confirm();

    EzPayInvoice::assertSent(Allowance::class, 'pending', function (TriggerOptions $options) {
        return $options->status === TriggerStatus::YES
            && $options->allowanceNo === 'A250726001830959'
            && $options->orderNo === 'Order001'
            && $options->totalAmount === 420;
    });

    expect($result)->toBeInstanceOf(TriggerResult::class)
        ->and($result->allowanceAmount())->toBe(420)
        ->and($result->remainingAmount())->toBe(0);
});

test('折讓觸發 → 模擬取消折讓', function () {
    EzPayInvoice::fake([
        TriggerResult::make([
            'Status' => 'SUCCESS',
            'Message' => '發票折讓刪除成功',
            'Result' => [
                'CheckCode' => '123456789',
                'AllowanceNo' => 'A250726001830959',
                'InvoiceNumber' => 'GG72002018',
                'MerchantID' => '111335678',
                'MerchantOrderNo' => 'Order001',
                'AllowanceAmt' => '0',
                'RemainAmt' => '0',
            ],
        ]),
    ]);

    $result = EzPayInvoice::allowance()
        ->pending()
        ->withAllowance('A250726001830959')
        ->withOrder('Order001')
        ->withTotalAmount(420)
        ->cancel();

    EzPayInvoice::assertSent(Allowance::class, 'pending', function (TriggerOptions $options) {
        return $options->status === TriggerStatus::NO
            && $options->allowanceNo === 'A250726001830959'
            && $options->orderNo === 'Order001'
            && $options->totalAmount === 420;
    });

    expect($result)->toBeInstanceOf(TriggerResult::class)
        ->and($result->allowanceAmount())->toBe(0)
        ->and($result->remainingAmount())->toBe(0);
});
