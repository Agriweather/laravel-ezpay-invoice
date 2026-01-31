<?php

use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Facades\EzPayInvoice;
use Agriweather\EzPayInvoice\Options\CodeValidation\CodeValidationOptions;
use Agriweather\EzPayInvoice\Options\Options;
use Agriweather\EzPayInvoice\Resources\CodeValidation;
use Agriweather\EzPayInvoice\Results\CodeValidation\CodeValidationResult;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\partialMock;

test('手機條碼驗證 → 可以驗證有效的手機條碼', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptByAES')->andReturn('encrypted_data');
    $crypto->expects('decryptByAES')->with('encrypted_data')->andReturn([
        'CellphoneBarcode' => '/ABC.123',
        'IsExist' => 'Y',
    ]);

    Http::fake([
        '*' => Http::response([
            'Status' => 'SUCCESS',
            'Message' => '查詢成功',
            'APIID' => 'barCodeCheck',
            'Version' => '1.0',
            'MerchantID' => '111335678',
            'Result' => 'encrypted_data',
            'CheckCode' => '123456789',
        ], 200),
    ]);

    $result = EzPayInvoice::codeValidation()
        ->withBarcode('/ABC.123')
        ->onPreparedOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'TimeStamp' => Carbon::now()->timestamp,
                'CellphoneBarcode' => '/ABC.123',
            ]);
        })
        ->check();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api_inv_application/checkBarCode';
    });

    expect($result)->toBeInstanceOf(CodeValidationResult::class)
        ->and($result->barcode())->toBe('/ABC.123')
        ->and($result->isValid())->toBeTrue();
});

test('手機條碼驗證 → 可以驗證無效的手機條碼', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptByAES')->andReturn('encrypted_data');
    $crypto->expects('decryptByAES')->with('encrypted_data')->andReturn([
        'CellphoneBarcode' => '/ABC.123',
        'IsExist' => 'N',
    ]);

    Http::fake([
        '*' => Http::response([
            'Status' => 'SUCCESS',
            'Message' => '查詢成功',
            'APIID' => 'barCodeCheck',
            'Version' => '1.0',
            'MerchantID' => '111335678',
            'Result' => 'encrypted_data',
            'CheckCode' => '123456789',
        ], 200),
    ]);

    $result = EzPayInvoice::codeValidation()
        ->withBarcode('/ABC.123')
        ->onPreparedOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'TimeStamp' => Carbon::now()->timestamp,
                'CellphoneBarcode' => '/ABC.123',
            ]);
        })
        ->check();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api_inv_application/checkBarCode';
    });

    expect($result)->toBeInstanceOf(CodeValidationResult::class)
        ->and($result->barcode())->toBe('/ABC.123')
        ->and($result->isValid())->toBeFalse();
});

test('手機條碼驗證 → 模擬驗證有效的手機條碼', function () {
    EzPayInvoice::fake([
        CodeValidationResult::make([
            'Status' => 'SUCCESS',
            'Message' => '查詢成功',
            'APIID' => 'barCodeCheck',
            'Version' => '1.0',
            'MerchantID' => '111335678',
            'Result' => [
                'CellphoneBarcode' => '/ABC.123',
                'IsExist' => 'Y',
            ],
            'CheckCode' => '123456789',
        ]),
    ]);

    $result = EzPayInvoice::codeValidation()
        ->withBarcode('/ABC.123')
        ->check();

    EzPayInvoice::assertSent(CodeValidation::class, function (CodeValidationOptions $options) {
        return $options->barcode === '/ABC.123';
    });

    expect($result)->toBeInstanceOf(CodeValidationResult::class)
        ->and($result->barcode())->toBe('/ABC.123')
        ->and($result->isValid())->toBeTrue();
});

test('手機條碼驗證 → 模擬驗證無效的手機條碼', function () {
    EzPayInvoice::fake([
        CodeValidationResult::make([
            'Status' => 'SUCCESS',
            'Message' => '查詢成功',
            'APIID' => 'barCodeCheck',
            'Version' => '1.0',
            'MerchantID' => '111335678',
            'Result' => [
                'CellphoneBarcode' => '/ABC.123',
                'IsExist' => 'N',
            ],
            'CheckCode' => '123456789',
        ]),
    ]);

    $result = EzPayInvoice::codeValidation()
        ->withBarcode('/ABC.123')
        ->check();

    EzPayInvoice::assertSent(CodeValidation::class, function (CodeValidationOptions $options) {
        return $options->barcode === '/ABC.123';
    });

    expect($result)->toBeInstanceOf(CodeValidationResult::class)
        ->and($result->barcode())->toBe('/ABC.123')
        ->and($result->isValid())->toBeFalse();
});
