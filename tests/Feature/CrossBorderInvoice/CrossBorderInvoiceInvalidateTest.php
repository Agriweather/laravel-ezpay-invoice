<?php

use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Facades\EzPayInvoice;
use Agriweather\EzPayInvoice\Options\CrossBorderInvoice\InvalidateOptions;
use Agriweather\EzPayInvoice\Options\Options;
use Agriweather\EzPayInvoice\Resources\CrossBorderInvoice;
use Agriweather\EzPayInvoice\Results\CrossBorderInvoice\InvalidateResult;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\partialMock;

test('境外電商發票作廢 → 可以作廢已開立的發票', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptByAES')->andReturn('encrypted_data');

    Http::fake([
        '*' => Http::response([
            'Status' => 'SUCCESS',
            'Message' => '電子發票作廢開立成功',
            'Result' => json_encode([
                'CheckCode' => '123456789',
                'MerchantID' => '111335678',
                'InvoiceNumber' => 'CB00000016',
                'CreateTime' => '2025-01-01 00:00:00',
            ]),
        ], 200),
    ]);

    $result = EzPayInvoice::crossBorder()
        ->invoice()
        ->voidable()
        ->withInvoice('CB00000016')
        ->because('客戶取消訂單')
        ->transformOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'InvoiceNumber' => 'CB00000016',
                'InvalidReason' => '客戶取消訂單',
            ]);

            return $options;
        })
        ->invalidate();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api/invoice_invalid';
    });

    expect($result)->toBeInstanceOf(InvalidateResult::class)
        ->and($result->invoiceNumber())->toBe('CB00000016');
});

test('境外電商發票作廢 → 模擬作廢已開立的發票', function () {
    EzPayInvoice::fake([
        InvalidateResult::make([
            'Status' => 'SUCCESS',
            'Message' => '電子發票作廢開立成功',
            'Result' => [
                'CheckCode' => '123456789',
                'MerchantID' => '111335678',
                'InvoiceNumber' => 'CB00000016',
                'CreateTime' => '2025-01-01 00:00:00',
            ],
        ]),
    ]);

    $result = EzPayInvoice::crossBorder()
        ->invoice()
        ->voidable()
        ->withInvoice('CB00000016')
        ->because('客戶取消訂單')
        ->invalidate();

    EzPayInvoice::assertSent(CrossBorderInvoice::class, 'voidable', function (InvalidateOptions $options) {
        return $options->invoiceNumber === 'CB00000016'
            && $options->invalidReason === '客戶取消訂單';
    });

    expect($result)->toBeInstanceOf(InvalidateResult::class)
        ->and($result->invoiceNumber())->toBe('CB00000016');
});
