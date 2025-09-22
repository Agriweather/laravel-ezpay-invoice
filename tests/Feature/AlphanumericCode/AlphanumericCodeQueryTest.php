<?php

use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Enums\AlphanumericCode\AlphanumericCodeStatus;
use Agriweather\EzPayInvoice\Enums\Invoice\InvoiceTerm;
use Agriweather\EzPayInvoice\Enums\Invoice\InvoiceType;
use Agriweather\EzPayInvoice\Facades\EzPayInvoice;
use Agriweather\EzPayInvoice\Options\AlphanumericCode\QueryOptions;
use Agriweather\EzPayInvoice\Options\Options;
use Agriweather\EzPayInvoice\Resources\AlphanumericCode;
use Agriweather\EzPayInvoice\Results\AlphanumericCode\QueryResult;
use Agriweather\EzPayInvoice\Results\AlphanumericCode\QueryResults;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\partialMock;

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
                    'Flag' => '1',
                    'CheckCode' => '123456789',
                ],
            ],
        ], 200),
    ]);

    $alphanumericCodeResults = EzPayInvoice::alphanumericCode()
        ->query()
        ->withYear(113)
        ->withTerm(InvoiceTerm::JUL_AUG)
        ->onPrepareOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'Year' => '113',
                'Term' => '4',
            ]);
        })
        ->get();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api_number_management/searchNumber';
    });

    expect($alphanumericCodeResults)->toBeInstanceOf(QueryResults::class)
        ->and($alphanumericCodeResults)->toHaveCount(1)
        ->and($alphanumericCodeResults[0])->toBeInstanceOf(QueryResult::class)
        ->and($alphanumericCodeResults[0]->managementNo())->toBe('0t0ghr0fyv')
        ->and($alphanumericCodeResults[0]->year())->toBe(113)
        ->and($alphanumericCodeResults[0]->term())->toBe(InvoiceTerm::JUL_AUG)
        ->and($alphanumericCodeResults[0]->alphanumericCode())->toBe('AA')
        ->and($alphanumericCodeResults[0]->startNumber())->toBe('24000100')
        ->and($alphanumericCodeResults[0]->endNumber())->toBe('24000199')
        ->and($alphanumericCodeResults[0]->type())->toBe(InvoiceType::GENERAL)
        ->and($alphanumericCodeResults[0]->lastNumber())->toBe(100)
        ->and($alphanumericCodeResults[0]->status())->toBe(AlphanumericCodeStatus::ENABLED);

    foreach ($alphanumericCodeResults as $result) {
        expect($result)->toBeInstanceOf(QueryResult::class);
    }
});

test('字軌管理 → 模擬查詢字軌資訊', function () {
    EzPayInvoice::fake([
        QueryResults::make([
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
                    'Flag' => '1',
                    'CheckCode' => '123456789',
                ],
            ],
        ]),
    ]);

    $alphanumericCodeResults = EzPayInvoice::alphanumericCode()
        ->query()
        ->withYear(113)
        ->withTerm(InvoiceTerm::JUL_AUG)
        ->get();

    EzPayInvoice::assertSent(AlphanumericCode::class, 'query', function (QueryOptions $options) {
        return $options->year === 113
            && $options->term === InvoiceTerm::JUL_AUG;
    });

    expect($alphanumericCodeResults)->toBeInstanceOf(QueryResults::class)
        ->and($alphanumericCodeResults[0])->toBeInstanceOf(QueryResult::class);
});
