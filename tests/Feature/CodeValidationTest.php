<?php

use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Facades\EzPayInvoice;
use Agriweather\EzPayInvoice\Options\Options;
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
        ->transformOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'TimeStamp' => Carbon::now()->timestamp,
                'CellphoneBarcode' => '/ABC.123',
            ]);

            return $options;
        })
        ->withBarcode('/ABC.123')
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
        ->transformOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'TimeStamp' => Carbon::now()->timestamp,
                'CellphoneBarcode' => '/ABC.123',
            ]);

            return $options;
        })
        ->withBarcode('/ABC.123')
        ->check();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api_inv_application/checkBarCode';
    });

    expect($result)->toBeInstanceOf(CodeValidationResult::class)
        ->and($result->barcode())->toBe('/ABC.123')
        ->and($result->isValid())->toBeFalse();
});

test('捐贈碼驗證 → 可以驗證有效的捐贈碼', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptByAES')->andReturn('encrypted_data');
    $crypto->expects('decryptByAES')->with('encrypted_data')->andReturn([
        'Lovecode' => '123',
        'IsExist' => 'Y',
    ]);

    Http::fake([
        '*' => Http::response([
            'Status' => 'SUCCESS',
            'Message' => '查詢成功',
            'APIID' => 'LoveCodeCheck',
            'Version' => '1.0',
            'MerchantID' => '111335678',
            'Result' => 'encrypted_data',
            'CheckCode' => '123456789',
        ], 200),
    ]);

    $result = EzPayInvoice::codeValidation()
        ->transformOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'TimeStamp' => Carbon::now()->timestamp,
                'LoveCode' => '123',
            ]);

            return $options;
        })
        ->withLoveCode('123')
        ->check();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api_inv_application/checkLoveCode';
    });

    expect($result)->toBeInstanceOf(CodeValidationResult::class)
        ->and($result->loveCode())->toBe('123')
        ->and($result->isValid())->toBeTrue();
});

test('捐贈碼驗證 → 可以驗證無效的捐贈碼', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptByAES')->andReturn('encrypted_data');
    $crypto->expects('decryptByAES')->with('encrypted_data')->andReturn([
        'Lovecode' => '123',
        'IsExist' => 'N',
    ]);

    Http::fake([
        '*' => Http::response([
            'Status' => 'SUCCESS',
            'Message' => '查詢成功',
            'APIID' => 'LoveCodeCheck',
            'Version' => '1.0',
            'MerchantID' => '111335678',
            'Result' => 'encrypted_data',
            'CheckCode' => '123456789',
        ], 200),
    ]);

    $result = EzPayInvoice::codeValidation()
        ->transformOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'TimeStamp' => Carbon::now()->timestamp,
                'LoveCode' => '123',
            ]);

            return $options;
        })
        ->withLoveCode('123')
        ->check();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api_inv_application/checkLoveCode';
    });

    expect($result)->toBeInstanceOf(CodeValidationResult::class)
        ->and($result->loveCode())->toBe('123')
        ->and($result->isValid())->toBeFalse();
});
