<?php

use Agriweather\EzpayInvoice\Factory;
use Agriweather\EzpayInvoice\Results\CodeValidationResult;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

describe('驗證功能測試', function () {
    beforeEach(function () {
        $this->factory = app(Factory::class);
    });

    describe('手機條碼驗證功能', function () {
        it('可以驗證有效的手機條碼', function () {
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

            $result = $this->factory
                ->codeValidation()
                ->checkBarcode('/ABC.122');

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api_inv_application/checkBarCode';
            });

            $this->factory->assertSentPostData([
                'TimeStamp' => time(),
                'CellphoneBarcode' => '/AAA.CCC',
            ]);

            expect($result)->toBeInstanceOf(CodeValidationResult::class)
                ->and($result->isValid())->toBeTrue();
        });

        it('可以驗證無效的手機條碼', function () {
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

            $result = $this->factory
                ->codeValidation()
                ->checkBarcode('/ABC.122');

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api_inv_application/checkBarCode';
            });

            $this->factory->assertSentPostData([
                'TimeStamp' => time(),
                'CellphoneBarcode' => '/AAA.CCC',
            ]);

            expect($result)->toBeInstanceOf(CodeValidationResult::class)
                ->and($result->isValid())->toBeFalse();
        });
    });

    describe('捐贈碼驗證功能', function () {
        it('可以驗證有效的捐贈碼', function () {
            // $this->mockLoveCodeVerificationSuccess([
            //     'IsValid' => true,
            //     'LoveCode' => 123,
            //     'OrganizationName' => '財團法人創世社會福利基金會',
            //     'BAN' => '04259011',
            //     'Status' => 1,
            // ]);

            // $result = $this->factory
            //     ->validation()
            //     ->withLoveCode(123)
            //     ->check();

            // expect($result)->toBeArray()
            //     ->and($result['IsValid'])->toBeTrue()
            //     ->and($result['LoveCode'])->toBe(123)
            //     ->and($result['OrganizationName'])->toBe('財團法人創世社會福利基金會');
        })->todo();

        it('可以驗證無效的捐贈碼', function () {
            // $this->mockLoveCodeVerificationSuccess([
            //     'IsValid' => false,
            //     'LoveCode' => 999,
            //     'ErrorMessage' => '捐贈碼不存在',
            //     'Status' => 0,
            // ]);

            // $result = $this->factory
            //     ->validation()
            //     ->withLoveCode(999)
            //     ->check();

            // expect($result)->toBeArray()
            //     ->and($result['IsValid'])->toBeFalse()
            //     ->and($result['ErrorMessage'])->toBe('捐贈碼不存在');
        })->todo();
    });
});
