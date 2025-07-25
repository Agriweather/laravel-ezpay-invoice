<?php

use Agriweather\EzpayInvoice\Factory;

describe('驗證功能測試', function () {
    beforeEach(function () {
        $this->factory = app(Factory::class);
    });

    describe('手機條碼驗證功能', function () {
        it('可以驗證有效的發票條碼', function () {
            // $this->mockBarcodeVerificationSuccess([
            //     'IsValid' => true,
            //     'InvoiceNumber' => 'AA12345678',
            //     'RandomNum' => '1234',
            //     'TotalAmount' => '105',
            //     'BuyerName' => 'John Doe',
            //     'SellerName' => '測試商家',
            //     'InvoiceDate' => '2024-01-01',
            //     'Status' => 1,
            // ]);

            // $result = $this->factory
            //     ->validation()
            //     ->withBarcode('/ABC.122')
            //     ->check();

            // expect($result)->toBeArray()
            //     ->and($result['IsValid'])->toBeTrue()
            //     ->and($result['InvoiceNumber'])->toBe('AA12345678')
            //     ->and($result['TotalAmount'])->toBe('105');
        })->todo();

        it('可以驗證無效的發票條碼', function () {
            // $this->mockBarcodeVerificationSuccess([
            //     'IsValid' => false,
            //     'ErrorMessage' => '條碼格式錯誤',
            //     'Status' => 0,
            // ]);

            // $result = $this->factory
            //     ->validation()
            //     ->withBarcode('/INVALID.BARCODE')
            //     ->check();

            // expect($result)->toBeArray()
            //     ->and($result['IsValid'])->toBeFalse()
            //     ->and($result['ErrorMessage'])->toBe('條碼格式錯誤');
        })->todo();

        it('可以驗證手機條碼格式', function () {
            // $this->mockBarcodeVerificationSuccess([
            //     'IsValid' => true,
            //     'BarcodeType' => 'MOBILE',
            //     'InvoiceNumber' => 'BB87654321',
            // ]);

            // $result = $this->factory
            //     ->validation()
            //     ->withBarcode('/ABC+123')
            //     ->check();

            // expect($result)->toBeArray()
            //     ->and($result['IsValid'])->toBeTrue()
            //     ->and($result['BarcodeType'])->toBe('MOBILE');
        })->todo();

        it('可以驗證自然人憑證條碼', function () {
            // $this->mockBarcodeVerificationSuccess([
            //     'IsValid' => true,
            //     'BarcodeType' => 'CITIZEN_CARD',
            //     'InvoiceNumber' => 'CC11111111',
            // ]);

            // $result = $this->factory
            //     ->validation()
            //     ->withBarcode('/ABCD123')
            //     ->check();

            // expect($result)->toBeArray()
            //     ->and($result['IsValid'])->toBeTrue()
            //     ->and($result['BarcodeType'])->toBe('CITIZEN_CARD');
        })->todo();
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

        it('可以驗證多位數捐贈碼', function () {
            // $this->mockLoveCodeVerificationSuccess([
            //     'IsValid' => true,
            //     'LoveCode' => 25885,
            //     'OrganizationName' => '社團法人中華民國身心障礙聯盟',
            //     'BAN' => '38552600',
            // ]);

            // $result = $this->factory
            //     ->validation()
            //     ->withLoveCode(25885)
            //     ->check();

            // expect($result)->toBeArray()
            //     ->and($result['IsValid'])->toBeTrue()
            //     ->and($result['LoveCode'])->toBe(25885);
        })->todo();
    });
});
