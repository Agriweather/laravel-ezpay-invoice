<?php

use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Facades\EzPayInvoice;
use Agriweather\EzPayInvoice\Options\CrossBorderInvoice\TriggerOptions;
use Agriweather\EzPayInvoice\Options\Options;
use Agriweather\EzPayInvoice\Resources\CrossBorderInvoice;
use Agriweather\EzPayInvoice\Results\CrossBorderInvoice\TriggerResult;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\partialMock;

test('境外電商發票觸發 → 可以觸發等待中的發票', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptByAES')->andReturn('encrypted_data');
    $crypto->expects('verifyCheckCode')->andReturnNull();

    Http::fake([
        '*' => Http::response([
            'Status' => 'SUCCESS',
            'Message' => '觸發開立發票成功',
            'Result' => json_encode([
                'CheckCode' => '123456789',
                'MerchantID' => '111335678',
                'MerchantOrderNo' => 'CBOrder001',
                'InvoiceNumber' => 'CB00000016',
                'TotalAmt' => '105.50',
                'InvoiceTransNo' => '25080200501024251',
                'RandomNum' => '1234',
                'CreateTime' => '2025-01-01 00:00:00',
            ]),
        ], 200),
    ]);

    $result = EzPayInvoice::crossBorder()
        ->invoice()
        ->pending()
        ->withInvoiceTransNo('25080200501024251')
        ->withOrder('CBOrder001')
        ->withTotalAmount(105.5)
        ->transformOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'InvoiceTransNo' => '25080200501024251',
                'MerchantOrderNo' => 'CBOrder001',
                'TotalAmt' => '105.5',
            ]);

            return $options;
        })
        ->trigger();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api/invoice_touch_issue';
    });

    expect($result)->toBeInstanceOf(TriggerResult::class)
        ->and($result->invoiceNumber())->toBe('CB00000016');
});

test('境外電商發票觸發 → 模擬觸發等待中的發票', function () {
    EzPayInvoice::fake([
        TriggerResult::make([
            'Status' => 'SUCCESS',
            'Message' => '觸發開立發票成功',
            'Result' => [
                'CheckCode' => '123456789',
                'MerchantID' => '111335678',
                'MerchantOrderNo' => 'CBOrder001',
                'InvoiceNumber' => 'CB00000016',
                'TotalAmt' => '105.50',
                'InvoiceTransNo' => '25080200501024251',
                'RandomNum' => '1234',
                'CreateTime' => '2025-01-01 00:00:00',
            ],
        ]),
    ]);

    $result = EzPayInvoice::crossBorder()
        ->invoice()
        ->pending()
        ->withInvoiceTransNo('25080200501024251')
        ->withOrder('CBOrder001')
        ->withTotalAmount(105.5)
        ->trigger();

    EzPayInvoice::assertSent(CrossBorderInvoice::class, 'pending', function (TriggerOptions $options) {
        return $options->invoiceTransNo === '25080200501024251'
            && $options->orderNo === 'CBOrder001'
            && $options->totalAmount === 105.5;
    });

    expect($result)->toBeInstanceOf(TriggerResult::class)
        ->and($result->invoiceNumber())->toBe('CB00000016');
});
