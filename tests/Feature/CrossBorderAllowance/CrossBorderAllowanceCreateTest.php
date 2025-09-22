<?php

use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Facades\EzPayInvoice;
use Agriweather\EzPayInvoice\Options\CrossBorderAllowance\CreateOptions;
use Agriweather\EzPayInvoice\Options\Options;
use Agriweather\EzPayInvoice\Resources\CrossBorderAllowance;
use Agriweather\EzPayInvoice\Results\CrossBorderAllowance\CreateResult;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\partialMock;

test('境外電商折讓開立 → 可以開立境外電商折讓', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptByAES')->andReturn('encrypted_data');

    Http::fake([
        '*' => Http::response([
            'Status' => 'SUCCESS',
            'Message' => '發票折讓開立成功',
            'Result' => json_encode([
                'CheckCode' => '123456789',
                'AllowanceNo' => 'A250802013300379',
                'InvoiceNumber' => 'CB00000022',
                'MerchantID' => '111335678',
                'MerchantOrderNo' => 'CBOrder001',
                'AllowanceAmt' => '105.50',
                'RemainAmt' => '0.00',
            ]),
        ], 200),
    ]);

    $result = EzPayInvoice::crossBorder()
        ->allowance()
        ->create()
        ->withInvoice('CB00000022')
        ->withOrder('CBOrder001')
        ->withItem('退貨商品', quantity: 1, unit: 'EA', price: 105.5, amount: 105.5)
        ->withTotalAmount(105.5)
        ->withNotification('customer@example.com')
        ->onPrepareOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'InvoiceNo' => 'CB00000022',
                'MerchantOrderNo' => 'CBOrder001',
                'ItemName' => '退貨商品',
                'ItemCount' => '1',
                'ItemUnit' => 'EA',
                'ItemPrice' => '105.5',
                'ItemAmt' => '105.5',
                'ItemTaxAmt' => '0',
                'TotalAmt' => '105.5',
                'BuyerEmail' => 'customer@example.com',
                'Status' => '1',
            ]);
        })
        ->issue();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api/crossBorderAllowanceIssue';
    });

    expect($result)->toBeInstanceOf(CreateResult::class)
        ->and($result->checkCode())->toBe('123456789')
        ->and($result->allowanceNo())->toBe('A250802013300379')
        ->and($result->orderNo())->toBe('CBOrder001')
        ->and($result->invoiceNumber())->toBe('CB00000022')
        ->and($result->allowanceAmount())->toBe(105.5)
        ->and($result->remainingAmount())->toBe(0.0);
});

test('境外電商折讓開立 → 模擬開立境外電商折讓', function () {
    EzPayInvoice::fake([
        CreateResult::make([
            'Status' => 'SUCCESS',
            'Message' => '發票折讓開立成功',
            'Result' => [
                'CheckCode' => '123456789',
                'AllowanceNo' => 'A250802013300379',
                'InvoiceNumber' => 'CB00000022',
                'MerchantID' => '111335678',
                'MerchantOrderNo' => 'CBOrder001',
                'AllowanceAmt' => '105.50',
                'RemainAmt' => '0.00',
            ],
        ]),
    ]);

    $result = EzPayInvoice::crossBorder()
        ->allowance()
        ->create()
        ->withInvoice('CB00000022')
        ->withOrder('CBOrder001')
        ->withItem('退貨商品', quantity: 1, unit: 'EA', price: 105.5, amount: 105.5)
        ->withTotalAmount(105.5)
        ->withNotification('customer@example.com')
        ->issue();

    EzPayInvoice::assertSent(CrossBorderAllowance::class, 'create', function (CreateOptions $options) {
        return $options->invoiceNo === 'CB00000022'
            && $options->orderNo === 'CBOrder001'
            && $options->totalAmount === 105.5;
    });

    expect($result)->toBeInstanceOf(CreateResult::class)
        ->and($result->allowanceNo())->toBe('A250802013300379');
});
