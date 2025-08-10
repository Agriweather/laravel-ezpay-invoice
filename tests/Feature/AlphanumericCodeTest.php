<?php

use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Enums\AlphanumericCode\AlphanumericCodeStatus;
use Agriweather\EzPayInvoice\Enums\Invoice\InvoiceTerm;
use Agriweather\EzPayInvoice\Enums\Invoice\InvoiceType;
use Agriweather\EzPayInvoice\Facades\EzPayInvoice;
use Agriweather\EzPayInvoice\Options\Options;
use Agriweather\EzPayInvoice\Results\AlphanumericCode\CreateResult;
use Agriweather\EzPayInvoice\Results\AlphanumericCode\QueryResult;
use Agriweather\EzPayInvoice\Results\AlphanumericCode\UpdateResult;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\partialMock;

test('字軌管理 → 可以成功申請新字軌', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptByAES')->andReturn('encrypted_data');

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

    $result = EzPayInvoice::alphanumericCode()
        ->create()
        ->withYear(113)
        ->withTerm(InvoiceTerm::JUL_AUG)
        ->withCode('AA')
        ->withRange('24000100', '24000199')
        ->withType(InvoiceType::GENERAL)
        ->transformOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
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

            return $options;
        })
        ->save();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api_number_management/createNumber';
    });

    expect($result)->toBeInstanceOf(CreateResult::class)
        ->and($result->managementNo())->toBe('0t0ghr0fyv')
        ->and($result->year())->toBe(113)
        ->and($result->term())->toBe(InvoiceTerm::JUL_AUG)
        ->and($result->alphabeticLetter())->toBe('AA')
        ->and($result->startNumber())->toBe('24000100')
        ->and($result->endNumber())->toBe('24000199')
        ->and($result->type())->toBe(InvoiceType::GENERAL)
        ->and($result->lastNumber())->toBe(100)
        ->and($result->status())->toBe(AlphanumericCodeStatus::ENABLED);
});

test('字軌管理 → 可以查詢字軌資訊', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptByAES')->andReturn('encrypted_data');

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

    $alphanumericCodeResults = EzPayInvoice::alphanumericCode()
        ->query()
        ->withYear(113)
        ->withTerm(InvoiceTerm::JUL_AUG)
        ->transformOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'Year' => '113',
                'Term' => '4',
            ]);

            return $options;
        })
        ->get();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api_number_management/searchNumber';
    });

    expect($alphanumericCodeResults)->toBeArray()->toHaveCount(1)
        ->and($alphanumericCodeResults[0])->toBeInstanceOf(QueryResult::class)
        ->and($alphanumericCodeResults[0]->managementNo())->toBe('0t0ghr0fyv')
        ->and($alphanumericCodeResults[0]->year())->toBe(113)
        ->and($alphanumericCodeResults[0]->term())->toBe(InvoiceTerm::JUL_AUG)
        ->and($alphanumericCodeResults[0]->alphabeticLetter())->toBe('AA')
        ->and($alphanumericCodeResults[0]->startNumber())->toBe('24000100')
        ->and($alphanumericCodeResults[0]->endNumber())->toBe('24000199')
        ->and($alphanumericCodeResults[0]->type())->toBe(InvoiceType::GENERAL)
        ->and($alphanumericCodeResults[0]->lastNumber())->toBe(100);
});

test('字軌管理 → 可以暫停字軌', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptByAES')->andReturn('encrypted_data');

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

    $result = EzPayInvoice::alphanumericCode()
        ->query()
        ->withNo('0t0ghr0fyv')
        ->withYear(113)
        ->transformOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'ManagementNo' => '0t0ghr0fyv',
                'Year' => '113',
                'Flag' => '0',
            ]);

            return $options;
        })
        ->pause();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api_number_management/manageNumber';
    });

    expect($result)->toBeInstanceOf(UpdateResult::class)
        ->and($result->managementNo())->toBe('0t0ghr0fyv')
        ->and($result->status())->toBe(AlphanumericCodeStatus::PAUSED);
});

test('字軌管理 → 可以啟用字軌', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptByAES')->andReturn('encrypted_data');

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

    $result = EzPayInvoice::alphanumericCode()
        ->query()
        ->withNo('0t0ghr0fyv')
        ->withYear(113)
        ->transformOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'ManagementNo' => '0t0ghr0fyv',
                'Year' => '113',
                'Flag' => '1',
            ]);

            return $options;
        })
        ->enable();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api_number_management/manageNumber';
    });

    expect($result)->toBeInstanceOf(UpdateResult::class)
        ->and($result->managementNo())->toBe('0t0ghr0fyv')
        ->and($result->status())->toBe(AlphanumericCodeStatus::ENABLED);
});

test('字軌管理 → 可以停用字軌', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptByAES')->andReturn('encrypted_data');

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

    $result = EzPayInvoice::alphanumericCode()
        ->query()
        ->withNo('0t0ghr0fyv')
        ->withYear(113)
        ->transformOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'ManagementNo' => '0t0ghr0fyv',
                'Year' => '113',
                'Flag' => '2',
            ]);

            return $options;
        })
        ->disable();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api_number_management/manageNumber';
    });

    expect($result)->toBeInstanceOf(UpdateResult::class)
        ->and($result->managementNo())->toBe('0t0ghr0fyv')
        ->and($result->status())->toBe(AlphanumericCodeStatus::DISABLED);
});
