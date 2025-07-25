<?php

use Agriweather\EzpayInvoice\Factory;
use Agriweather\EzpayInvoice\Exceptions\EzpayInvoiceException;
use Tests\Concerns\MocksHttpRequests;

uses(MocksHttpRequests::class);

describe('驗證服務功能測試', function () {
    beforeEach(function () {
        $this->factory = app(Factory::class);
    });

    describe('條碼驗證功能', function () {
        it('使用者可以驗證有效的發票條碼', function () {
            $this->mockBarcodeVerificationSuccess([
                'IsValid' => true,
                'InvoiceNumber' => 'AA12345678',
                'RandomNum' => '1234',
                'TotalAmount' => '105',
                'BuyerName' => '王小明',
                'SellerName' => '測試商家',
                'InvoiceDate' => '2024-01-01',
                'Status' => 1,
            ]);

            $result = $this->factory
                ->validation()
                ->withBarcode('/ABC.122')
                ->check();

            expect($result)->toBeArray()
                ->and($result['IsValid'])->toBeTrue()
                ->and($result['InvoiceNumber'])->toBe('AA12345678')
                ->and($result['TotalAmount'])->toBe('105');
        });

        it('使用者可以驗證無效的發票條碼', function () {
            $this->mockBarcodeVerificationSuccess([
                'IsValid' => false,
                'ErrorMessage' => '條碼格式錯誤',
                'Status' => 0,
            ]);

            $result = $this->factory
                ->validation()
                ->withBarcode('/INVALID.BARCODE')
                ->check();

            expect($result)->toBeArray()
                ->and($result['IsValid'])->toBeFalse()
                ->and($result['ErrorMessage'])->toBe('條碼格式錯誤');
        });

        it('使用者可以驗證手機條碼格式', function () {
            $this->mockBarcodeVerificationSuccess([
                'IsValid' => true,
                'BarcodeType' => 'MOBILE',
                'InvoiceNumber' => 'BB87654321',
            ]);

            $result = $this->factory
                ->validation()
                ->withBarcode('/ABC+123')
                ->check();

            expect($result)->toBeArray()
                ->and($result['IsValid'])->toBeTrue()
                ->and($result['BarcodeType'])->toBe('MOBILE');
        });

        it('使用者可以驗證自然人憑證條碼', function () {
            $this->mockBarcodeVerificationSuccess([
                'IsValid' => true,
                'BarcodeType' => 'CITIZEN_CARD',
                'InvoiceNumber' => 'CC11111111',
            ]);

            $result = $this->factory
                ->validation()
                ->withBarcode('/ABCD123')
                ->check();

            expect($result)->toBeArray()
                ->and($result['IsValid'])->toBeTrue()
                ->and($result['BarcodeType'])->toBe('CITIZEN_CARD');
        });
    });

    describe('愛心碼驗證功能', function () {
        it('使用者可以驗證有效的愛心碼', function () {
            $this->mockLoveCodeVerificationSuccess([
                'IsValid' => true,
                'LoveCode' => 123,
                'OrganizationName' => '財團法人創世社會福利基金會',
                'BAN' => '04259011',
                'Status' => 1,
            ]);

            $result = $this->factory
                ->validation()
                ->withLoveCode(123)
                ->check();

            expect($result)->toBeArray()
                ->and($result['IsValid'])->toBeTrue()
                ->and($result['LoveCode'])->toBe(123)
                ->and($result['OrganizationName'])->toBe('財團法人創世社會福利基金會');
        });

        it('使用者可以驗證無效的愛心碼', function () {
            $this->mockLoveCodeVerificationSuccess([
                'IsValid' => false,
                'LoveCode' => 999,
                'ErrorMessage' => '愛心碼不存在',
                'Status' => 0,
            ]);

            $result = $this->factory
                ->validation()
                ->withLoveCode(999)
                ->check();

            expect($result)->toBeArray()
                ->and($result['IsValid'])->toBeFalse()
                ->and($result['ErrorMessage'])->toBe('愛心碼不存在');
        });

        it('使用者可以驗證多位數愛心碼', function () {
            $this->mockLoveCodeVerificationSuccess([
                'IsValid' => true,
                'LoveCode' => 25885,
                'OrganizationName' => '社團法人中華民國身心障礙聯盟',
                'BAN' => '38552600',
            ]);

            $result = $this->factory
                ->validation()
                ->withLoveCode(25885)
                ->check();

            expect($result)->toBeArray()
                ->and($result['IsValid'])->toBeTrue()
                ->and($result['LoveCode'])->toBe(25885);
        });
    });

    describe('批量驗證功能', function () {
        it('使用者可以批量驗證多個條碼', function () {
            $barcodes = ['/ABC.122', '/DEF.456', '/GHI.789'];
            $results = [];

            foreach ($barcodes as $barcode) {
                $this->mockBarcodeVerificationSuccess([
                    'IsValid' => true,
                    'InvoiceNumber' => 'TEST' . rand(10000000, 99999999),
                ]);

                $results[] = $this->factory
                    ->validation()
                    ->withBarcode($barcode)
                    ->check();
            }

            expect($results)->toHaveCount(3);
            foreach ($results as $result) {
                expect($result['IsValid'])->toBeTrue();
            }
        });

        it('使用者可以批量驗證多個愛心碼', function () {
            $loveCodes = [123, 456, 789];
            $results = [];

            foreach ($loveCodes as $loveCode) {
                $this->mockLoveCodeVerificationSuccess([
                    'IsValid' => true,
                    'LoveCode' => $loveCode,
                    'OrganizationName' => '測試機構 ' . $loveCode,
                ]);

                $results[] = $this->factory
                    ->validation()
                    ->withLoveCode($loveCode)
                    ->check();
            }

            expect($results)->toHaveCount(3);
            foreach ($results as $result) {
                expect($result['IsValid'])->toBeTrue();
            }
        });
    });

    describe('錯誤情境處理', function () {
        it('在條碼格式錯誤時應拋出例外', function () {
            $this->mockFailedApiResponse('LIB10050', '條碼格式錯誤');

            expect(fn() => $this->factory
                ->validation()
                ->withBarcode('INVALID_FORMAT')
                ->check())
                ->toThrow(EzpayInvoiceException::class, '條碼格式錯誤');
        });

        it('在愛心碼格式錯誤時應拋出例外', function () {
            $this->mockFailedApiResponse('LIB10051', '愛心碼格式錯誤');

            expect(fn() => $this->factory
                ->validation()
                ->withLoveCode(-1)
                ->check())
                ->toThrow(EzpayInvoiceException::class, '愛心碼格式錯誤');
        });

        it('在網路連線錯誤時應拋出例外', function () {
            $this->mockHttpConnectionError();

            expect(fn() => $this->factory
                ->validation()
                ->withBarcode('/ABC.122')
                ->check())
                ->toThrow(Exception::class);
        });

        it('在 API 回應格式錯誤時應拋出例外', function () {
            $this->mockInvalidApiResponse();

            expect(fn() => $this->factory
                ->validation()
                ->withLoveCode(123)
                ->check())
                ->toThrow(EzpayInvoiceException::class);
        });
    });

    describe('驗證結果快取功能', function () {
        it('相同條碼的驗證結果可以被快取', function () {
            $barcode = '/ABC.122';

            $this->mockBarcodeVerificationSuccess([
                'IsValid' => true,
                'InvoiceNumber' => 'AA12345678',
            ]);

            $result1 = $this->factory
                ->validation()
                ->withBarcode($barcode)
                ->check();

            $result2 = $this->factory
                ->validation()
                ->withBarcode($barcode)
                ->check();

            expect($result1)->toBe($result2);
        });

        it('相同愛心碼的驗證結果可以被快取', function () {
            $loveCode = 123;

            $this->mockLoveCodeVerificationSuccess([
                'IsValid' => true,
                'LoveCode' => 123,
                'OrganizationName' => '測試機構',
            ]);

            $result1 = $this->factory
                ->validation()
                ->withLoveCode($loveCode)
                ->check();

            $result2 = $this->factory
                ->validation()
                ->withLoveCode($loveCode)
                ->check();

            expect($result1)->toBe($result2);
        });
    });
});