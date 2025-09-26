<?php

use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Enums\AlphanumericCode\AlphanumericCodeStatus;
use Agriweather\EzPayInvoice\Facades\EzPayInvoice;
use Agriweather\EzPayInvoice\Options\AlphanumericCode\QueryOptions;
use Agriweather\EzPayInvoice\Options\Options;
use Agriweather\EzPayInvoice\Resources\AlphanumericCode;
use Agriweather\EzPayInvoice\Results\AlphanumericCode\UpdateResult;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\partialMock;

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
        ->onPreparedOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'ManagementNo' => '0t0ghr0fyv',
                'Year' => '113',
                'Flag' => '0',
            ]);
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
        ->onPreparedOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'ManagementNo' => '0t0ghr0fyv',
                'Year' => '113',
                'Flag' => '1',
            ]);
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
        ->onPreparedOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'ManagementNo' => '0t0ghr0fyv',
                'Year' => '113',
                'Flag' => '2',
            ]);
        })
        ->disable();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api_number_management/manageNumber';
    });

    expect($result)->toBeInstanceOf(UpdateResult::class)
        ->and($result->managementNo())->toBe('0t0ghr0fyv')
        ->and($result->status())->toBe(AlphanumericCodeStatus::DISABLED);
});

test('字軌管理 → 模擬暫停字軌', function () {
    EzPayInvoice::fake([
        UpdateResult::make([
            'Status' => 'SUCCESS',
            'Message' => '修改字軌狀態成功',
            'Result' => [
                'ManagementNo' => '0t0ghr0fyv',
                'Flag' => '0',
                'CheckCode' => '123456789',
            ],
        ]),
    ]);

    $result = EzPayInvoice::alphanumericCode()
        ->query()
        ->withNo('0t0ghr0fyv')
        ->withYear(113)
        ->pause();

    EzPayInvoice::assertSent(AlphanumericCode::class, 'query', function (QueryOptions $options) {
        return $options->managementNo === '0t0ghr0fyv'
            && $options->year === 113
            && $options->status === AlphanumericCodeStatus::PAUSED;
    });

    expect($result)->toBeInstanceOf(UpdateResult::class);
});

test('字軌管理 → 模擬啟用字軌', function () {
    EzPayInvoice::fake([
        UpdateResult::make([
            'Status' => 'SUCCESS',
            'Message' => '修改字軌狀態成功',
            'Result' => [
                'ManagementNo' => '0t0ghr0fyv',
                'Flag' => '1',
                'CheckCode' => '123456789',
            ],
        ]),
    ]);

    $result = EzPayInvoice::alphanumericCode()
        ->query()
        ->withNo('0t0ghr0fyv')
        ->withYear(113)
        ->enable();

    EzPayInvoice::assertSent(AlphanumericCode::class, 'query', function (QueryOptions $options) {
        return $options->managementNo === '0t0ghr0fyv'
            && $options->year === 113
            && $options->status === AlphanumericCodeStatus::ENABLED;
    });

    expect($result)->toBeInstanceOf(UpdateResult::class);
});

test('字軌管理 → 模擬停用字軌', function () {
    EzPayInvoice::fake([
        UpdateResult::make([
            'Status' => 'SUCCESS',
            'Message' => '修改字軌狀態成功',
            'Result' => [
                'ManagementNo' => '0t0ghr0fyv',
                'Flag' => '2',
                'CheckCode' => '123456789',
            ],
        ]),
    ]);

    $result = EzPayInvoice::alphanumericCode()
        ->query()
        ->withNo('0t0ghr0fyv')
        ->withYear(113)
        ->disable();

    EzPayInvoice::assertSent(AlphanumericCode::class, 'query', function (QueryOptions $options) {
        return $options->managementNo === '0t0ghr0fyv'
            && $options->year === 113
            && $options->status === AlphanumericCodeStatus::DISABLED;
    });

    expect($result)->toBeInstanceOf(UpdateResult::class);
});
