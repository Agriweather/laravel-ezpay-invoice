<?php

use Agriweather\EzpayInvoice\Crypto\EzpayCrypto;
use Agriweather\EzpayInvoice\Facades\EzpayInvoice;
use Agriweather\EzpayInvoice\Results\CodeValidationResult;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\partialMock;

describe('驗證功能測試', function () {
    describe('手機條碼驗證功能', function () {
        test('可以驗證有效的手機條碼', function () {
            $ezpayCrypto = partialMock(EzpayCrypto::class);
            $ezpayCrypto->expects('encryptPostData')->with([
                'TimeStamp' => Carbon::now()->timestamp,
                'CellphoneBarcode' => '/AAA.CCC',
            ])->andReturn('');

            Http::fake([
                '*' => Http::response([
                    'Status' => 'SUCCESS',
                    'Message' => '查詢成功',
                    'APIID' => 'barCodeCheck',
                    'Version' => '1.0',
                    'MerchantID' => '111335678',
                    'Result' => [
                        'CellphoneBarcode' => '/AAA.CCC',
                        'IsExist' => 'Y',
                    ],
                    'CheckCode' => '123456789',
                ], 200),
            ]);

            $result = EzpayInvoice::codeValidation()
                ->checkBarcode('/ABC.122');

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api_inv_application/checkBarCode';
            });

            expect($result)->toBeInstanceOf(CodeValidationResult::class)
                ->and($result->isValid())->toBeTrue();
        });

        test('可以驗證無效的手機條碼', function () {
            $ezpayCrypto = partialMock(EzpayCrypto::class);
            $ezpayCrypto->expects('encryptPostData')->with([
                'TimeStamp' => Carbon::now()->timestamp,
                'CellphoneBarcode' => '/AAA.CCC',
            ])->andReturn('');

            Http::fake([
                '*' => Http::response([
                    'Status' => 'SUCCESS',
                    'Message' => '查詢成功',
                    'APIID' => 'barCodeCheck',
                    'Version' => '1.0',
                    'MerchantID' => '111335678',
                    'Result' => [
                        'CellphoneBarcode' => '/AAA.CCC',
                        'IsExist' => 'N',
                    ],
                    'CheckCode' => '123456789',
                ], 200),
            ]);

            $result = EzpayInvoice::codeValidation()
                ->checkBarcode('/ABC.122');

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api_inv_application/checkBarCode';
            });

            expect($result)->toBeInstanceOf(CodeValidationResult::class)
                ->and($result->isValid())->toBeFalse();
        });
    });

    describe('捐贈碼驗證功能', function () {
        test('可以驗證有效的捐贈碼', function () {
            $ezpayCrypto = partialMock(EzpayCrypto::class);
            $ezpayCrypto->expects('encryptPostData')->with([
                'TimeStamp' => Carbon::now()->timestamp,
                'Lovecode' => 123,
            ])->andReturn('');

            Http::fake([
                '*' => Http::response([
                    'Status' => 'SUCCESS',
                    'Message' => '查詢成功',
                    'APIID' => 'LoveCodeCheck',
                    'Version' => '1.0',
                    'MerchantID' => '111335678',
                    'Result' => [
                        'Lovecode' => '123',
                        'IsExist' => 'Y',
                    ],
                    'CheckCode' => '123456789',
                ], 200),
            ]);

            $result = EzpayInvoice::codeValidation()
                ->checkLoveCode(123);

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api_inv_application/checkLoveCode';
            });

            expect($result)->toBeInstanceOf(CodeValidationResult::class)
                ->and($result->isValid())->toBeTrue();
        });

        test('可以驗證無效的捐贈碼', function () {
            $ezpayCrypto = partialMock(EzpayCrypto::class);
            $ezpayCrypto->expects('encryptPostData')->with([
                'TimeStamp' => Carbon::now()->timestamp,
                'Lovecode' => 123,
            ])->andReturn('');

            Http::fake([
                '*' => Http::response([
                    'Status' => 'SUCCESS',
                    'Message' => '查詢成功',
                    'APIID' => 'LoveCodeCheck',
                    'Version' => '1.0',
                    'MerchantID' => '111335678',
                    'Result' => [
                        'Lovecode' => '123',
                        'IsExist' => 'N',
                    ],
                    'CheckCode' => '123456789',
                ], 200),
            ]);

            $result = EzpayInvoice::codeValidation()
                ->checkLoveCode(123);

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api_inv_application/checkLoveCode';
            });

            expect($result)->toBeInstanceOf(CodeValidationResult::class)
                ->and($result->isValid())->toBeFalse();
        });
    });
});
