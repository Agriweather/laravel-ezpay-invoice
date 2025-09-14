<?php

use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Enums\AlphanumericCode\AlphanumericCodeStatus;
use Agriweather\EzPayInvoice\Enums\Invoice\InvoiceTerm;
use Agriweather\EzPayInvoice\Enums\Invoice\InvoiceType;
use Agriweather\EzPayInvoice\Facades\EzPayInvoice;
use Agriweather\EzPayInvoice\Options\AlphanumericCode\CreateOptions;
use Agriweather\EzPayInvoice\Options\Options;
use Agriweather\EzPayInvoice\Resources\AlphanumericCode;
use Agriweather\EzPayInvoice\Results\AlphanumericCode\CreateResult;
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
        ->and($result->alphanumericCode())->toBe('AA')
        ->and($result->startNumber())->toBe('24000100')
        ->and($result->endNumber())->toBe('24000199')
        ->and($result->type())->toBe(InvoiceType::GENERAL)
        ->and($result->lastNumber())->toBe(100)
        ->and($result->status())->toBe(AlphanumericCodeStatus::ENABLED);
});

test('字軌管理 → 模擬成功申請新字軌', function () {
    EzPayInvoice::fake([
        CreateResult::make([
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
        ]),
    ]);

    $result = EzPayInvoice::alphanumericCode()
        ->create()
        ->withYear(113)
        ->withTerm(InvoiceTerm::JUL_AUG)
        ->withCode('AA')
        ->withRange('24000100', '24000199')
        ->withType(InvoiceType::GENERAL)
        ->save();

    EzPayInvoice::assertSent(AlphanumericCode::class, 'create', function (CreateOptions $options) {
        return $options->year === 113
            && $options->term === InvoiceTerm::JUL_AUG
            && $options->alphanumericCode === 'AA'
            && $options->startNumber === '24000100'
            && $options->endNumber === '24000199'
            && $options->type === InvoiceType::GENERAL;
    });

    expect($result)->toBeInstanceOf(CreateResult::class)
        ->and($result->managementNo())->toBe('0t0ghr0fyv')
        ->and($result->year())->toBe(113)
        ->and($result->term())->toBe(InvoiceTerm::JUL_AUG)
        ->and($result->alphanumericCode())->toBe('AA')
        ->and($result->startNumber())->toBe('24000100')
        ->and($result->endNumber())->toBe('24000199')
        ->and($result->type())->toBe(InvoiceType::GENERAL)
        ->and($result->lastNumber())->toBe(100)
        ->and($result->status())->toBe(AlphanumericCodeStatus::ENABLED);
});
