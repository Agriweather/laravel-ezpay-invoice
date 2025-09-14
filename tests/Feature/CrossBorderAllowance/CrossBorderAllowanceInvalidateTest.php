<?php

use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Facades\EzPayInvoice;
use Agriweather\EzPayInvoice\Options\CrossBorderAllowance\InvalidateOptions;
use Agriweather\EzPayInvoice\Options\Options;
use Agriweather\EzPayInvoice\Resources\CrossBorderAllowance;
use Agriweather\EzPayInvoice\Results\CrossBorderAllowance\InvalidateResult;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\partialMock;

test('境外電商折讓作廢 → 可以作廢已開立的境外電商折讓', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptByAES')->andReturn('encrypted_data');

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

test('境外電商折讓作廢 → 模擬作廢已開立的境外電商折讓', function () {
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
