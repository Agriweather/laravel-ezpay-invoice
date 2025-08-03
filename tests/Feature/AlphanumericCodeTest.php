<?php

use Agriweather\EzpayInvoice\Crypto\EzpayCrypto;
use Agriweather\EzpayInvoice\Enums\AlphanumericCodeFlag;
use Agriweather\EzpayInvoice\Enums\InvoiceTerm;
use Agriweather\EzpayInvoice\Enums\InvoiceType;
use Agriweather\EzpayInvoice\Facades\EzpayInvoice;
use Agriweather\EzpayInvoice\Results\AlphanumericCodeResult;
use Agriweather\EzpayInvoice\Results\Result;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\partialMock;

describe('字軌管理功能測試', function () {
    describe('字軌申請功能', function () {
        it('可以成功申請新字軌', function () {
            partialMock(EzpayCrypto::class)->expects('encryptPostData')->with([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'Year' => '113',
                'Term' => '4',
                'AphabeticLetter' => 'AA',
                'StartNumber' => '24000100',
                'EndNumber' => '24000199',
                'Type' => '07',
            ]);

            Http::fake([
                '*' => Http::response([
                    'Status' => 'SUCCESS',
                    'Message' => '新增字軌成功',
                    'Result' => [
                        'ManagementNo' => '0t0ghr0fyv',
                        'Year' => '113',
                        'Term' => '4',
                        'AphabeticLetter' => 'AA',
                        'StartNumber' => '24000100',
                        'EndNumber' => '24000199',
                        'Type' => '07',
                        'CreateDatetime' => '2025-01-01 00:00:00',
                        'LastNumber' => '100',
                        'Flag' => '1',
                        'CheckCode' => '123456789',
                    ],
                ], 200),
            ]);

            $result = EzpayInvoice::alphanumericCode()
                ->create()
                ->withYear(113)
                ->withTerm(InvoiceTerm::FOURTH)
                ->withCode('AA')
                ->withRange('24000100', '24000199')
                ->withType(InvoiceType::GENERAL)
                ->save();

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api_number_management/createNumber';
            });

            expect($result)->toBeInstanceOf(Result::class)
                ->and($result->managementNo())->toBe('0t0ghr0fyv')
                ->and($result->year())->toBe(113)
                ->and($result->term())->toBe(InvoiceTerm::FOURTH)
                ->and($result->alphabeticLetter())->toBe('AA')
                ->and($result->startNumber())->toBe('24000100')
                ->and($result->endNumber())->toBe('24000199')
                ->and($result->type())->toBe(InvoiceType::GENERAL)
                ->and($result->lastNumber())->toBe(100)
                ->and($result->flag())->toBe(AlphanumericCodeFlag::ACTIVE);
        });
    });

    describe('字軌查詢功能', function () {
        it('可以查詢字軌資訊', function () {
            partialMock(EzpayCrypto::class)->expects('encryptPostData')->with([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'Year' => '114',
                'Term' => '4',
            ]);

            Http::fake([
                '*' => Http::response([
                    'Status' => 'SUCCESS',
                    'Message' => '查詢字軌成功',
                    'Result' => [
                        [
                            'ManagementNo' => '0t0ghr0fyv',
                            'Year' => '113',
                            'Term' => '4',
                            'AphabeticLetter' => 'AA',
                            'StartNumber' => '24000100',
                            'EndNumber' => '24000199',
                            'Type' => '07',
                            'CreateDatetime' => '2025-01-01 00:00:00',
                            'LastNumber' => '100',
                            'Flag' => '0',
                            'CheckCode' => '123456789',
                        ],
                    ],
                ], 200),
            ]);

            $alphanumericCodeResult = EzpayInvoice::alphanumericCode()
                ->query()
                // ->withNo('00455ujp8')
                ->withYear(113)
                ->withTerm(InvoiceTerm::FOURTH)
                // ->withStatus(1)
                // ->withPaused()
                // ->withEnabled()
                // ->withDisabled()
                ->get();

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api_number_management/createNumber';
            });

            expect($alphanumericCodeResult)->toBeInstanceOf(AlphanumericCodeResult::class)
                ->and($alphanumericCodeResult->managementNo)->toBe('0t0ghr0fyv')
                ->and($alphanumericCodeResult->year)->toBe(113)
                ->and($alphanumericCodeResult->term)->toBe(InvoiceTerm::FOURTH)
                ->and($alphanumericCodeResult->alphabeticLetter)->toBe('AA')
                ->and($alphanumericCodeResult->startNumber)->toBe('24000100')
                ->and($alphanumericCodeResult->endNumber)->toBe('24000199')
                ->and($alphanumericCodeResult->type)->toBe(InvoiceType::GENERAL)
                ->and($alphanumericCodeResult->lastNumber)->toBe(100);
        });
    });

    describe('字軌管理功能', function () {
        it('可以暫停字軌', function () {
            partialMock(EzpayCrypto::class)->expects('encryptPostData')->with([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'ManagementNo' => '0t0ghr0fyv',
                'Year' => '114',
                'Flag' => AlphanumericCodeFlag::PAUSED,
            ]);

            Http::fake([
                '*' => Http::response([
                    'Status' => 'SUCCESS',
                    'Message' => '修改字軌狀態成功',
                    'Result' => [
                        'ManagementNo' => '0t0ghr0fyv',
                        'Year' => '113',
                        'Term' => '4',
                        'AphabeticLetter' => 'AA',
                        'StartNumber' => '24000100',
                        'EndNumber' => '24000199',
                        'Type' => '07',
                        'CreateDatetime' => '2025-01-01 00:00:00',
                        'LastNumber' => '100',
                        'Flag' => '0',
                        'CheckCode' => '123456789',
                    ],
                ], 200),
            ]);

            $result = EzpayInvoice::alphanumericCode()
                ->query()
                ->withNo('00455ujp8')
                ->withYear(113)
                ->pause();

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api_number_management/manageNumber';
            });

            expect($result)->toBeInstanceOf(Result::class)
                ->and($result->managementNo)->toBe('0t0ghr0fyv')
                ->and($result->flag)->toBe(AlphanumericCodeFlag::PAUSED);
        });

        it('可以啟用字軌', function () {
            partialMock(EzpayCrypto::class)->expects('encryptPostData')->with([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'ManagementNo' => '0t0ghr0fyv',
                'Year' => '114',
                'Flag' => AlphanumericCodeFlag::ACTIVE,
            ]);

            Http::fake([
                '*' => Http::response([
                    'Status' => 'SUCCESS',
                    'Message' => '修改字軌狀態成功',
                    'Result' => [
                        'ManagementNo' => '0t0ghr0fyv',
                        'Year' => '113',
                        'Term' => '4',
                        'AphabeticLetter' => 'AA',
                        'StartNumber' => '24000100',
                        'EndNumber' => '24000199',
                        'Type' => '07',
                        'CreateDatetime' => '2025-01-01 00:00:00',
                        'LastNumber' => '100',
                        'Flag' => '1',
                        'CheckCode' => '123456789',
                    ],
                ], 200),
            ]);

            $result = EzpayInvoice::alphanumericCode()
                ->query()
                ->withNo('00455ujp8')
                ->withYear(113)
                ->active();

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api_number_management/manageNumber';
            });

            expect($result)->toBeInstanceOf(Result::class)
                ->and($result->managementNo)->toBe('0t0ghr0fyv')
                ->and($result->flag)->toBe(AlphanumericCodeFlag::ACTIVE);
        });

        it('可以停用字軌', function () {
            partialMock(EzpayCrypto::class)->expects('encryptPostData')->with([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'ManagementNo' => '0t0ghr0fyv',
                'Year' => '114',
                'Flag' => AlphanumericCodeFlag::DISABLED,
            ]);

            Http::fake([
                '*' => Http::response([
                    'Status' => 'SUCCESS',
                    'Message' => '修改字軌狀態成功',
                    'Result' => [
                        'ManagementNo' => '0t0ghr0fyv',
                        'Year' => '113',
                        'Term' => '4',
                        'AphabeticLetter' => 'AA',
                        'StartNumber' => '24000100',
                        'EndNumber' => '24000199',
                        'Type' => '07',
                        'CreateDatetime' => '2025-01-01 00:00:00',
                        'LastNumber' => '100',
                        'Flag' => '2',
                        'CheckCode' => '123456789',
                    ],
                ], 200),
            ]);

            $result = EzpayInvoice::alphanumericCode()
                ->query()
                ->withNo('00455ujp8')
                ->withYear(113)
                ->disable();

            Http::assertSent(function (Request $request) {
                return $request->url() == 'https://cinv.ezpay.com.tw/Api_number_management/manageNumber';
            });

            expect($result)->toBeInstanceOf(Result::class)
                ->and($result->managementNo)->toBe('0t0ghr0fyv')
                ->and($result->flag)->toBe(AlphanumericCodeFlag::DISABLED);
        });
    });
});
