<?php

use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Facades\EzPayInvoice;
use Agriweather\EzPayInvoice\Options\Invoice\TriggerOptions;
use Agriweather\EzPayInvoice\Options\Options;
use Agriweather\EzPayInvoice\Resources\Invoice;
use Agriweather\EzPayInvoice\Results\Invoice\TriggerResult;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\partialMock;

test('發票觸發 → 可以觸發等待中的發票', function () {
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
                'MerchantOrderNo' => 'Order004',
                'InvoiceNumber' => 'GG72002017',
                'TotalAmt' => '210',
                'InvoiceTransNo' => '25072516392250538',
                'RandomNum' => '1234',
                'CreateTime' => '2025-01-01 00:00:00',
            ]),
        ], 200),
    ]);

    $result = EzPayInvoice::invoice()
        ->pending()
        ->withInvoiceTransNo('25072516392250538')
        ->withOrder('Order004')
        ->withTotalAmount(210)
        ->onPrepareOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'InvoiceTransNo' => '25072516392250538',
                'MerchantOrderNo' => 'Order004',
                'TotalAmt' => '210',
            ]);
        })
        ->trigger();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api/invoice_touch_issue';
    });

    expect($result)->toBeInstanceOf(TriggerResult::class)
        ->and($result->invoiceNumber())->toBe('GG72002017');
});

test('發票觸發 → 模擬觸發等待中的發票', function () {
    EzPayInvoice::fake([
        TriggerResult::make([
            'Status' => 'SUCCESS',
            'Message' => '觸發開立發票成功',
            'Result' => [
                'CheckCode' => '123456789',
                'MerchantID' => '111335678',
                'MerchantOrderNo' => 'Order004',
                'InvoiceNumber' => 'GG72002017',
                'TotalAmt' => '210',
                'InvoiceTransNo' => '25072516392250538',
                'RandomNum' => '1234',
                'CreateTime' => '2025-01-01 00:00:00',
            ],
        ]),
    ]);

    $result = EzPayInvoice::invoice()
        ->pending()
        ->withInvoiceTransNo('25072516392250538')
        ->withOrder('Order004')
        ->withTotalAmount(210)
        ->trigger();

    EzPayInvoice::assertSent(Invoice::class, 'pending', function (TriggerOptions $options) {
        return $options->invoiceTransNo === '25072516392250538'
            && $options->orderNo === 'Order004'
            && $options->totalAmount === 210;
    });

    expect($result)->toBeInstanceOf(TriggerResult::class)
        ->and($result->invoiceNumber())->toBe('GG72002017');
});
