<?php

use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Enums\Allowance\CreateStatus;
use Agriweather\EzPayInvoice\Facades\EzPayInvoice;
use Agriweather\EzPayInvoice\Options\Allowance\CreateOptions;
use Agriweather\EzPayInvoice\Options\Options;
use Agriweather\EzPayInvoice\Resources\Allowance;
use Agriweather\EzPayInvoice\Results\Allowance\CreateResult;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\partialMock;

test('折讓開立 → 可以成功開立折讓', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptByAES')->andReturn('encrypted_data');

    Http::fake([
        '*' => Http::response([
            'Status' => 'SUCCESS',
            'Message' => '發票折讓開立成功',
            'Result' => json_encode([
                'CheckCode' => '123456789',
                'AllowanceNo' => 'A250725235346456',
                'InvoiceNumber' => 'GG72002018',
                'MerchantID' => '111335678',
                'MerchantOrderNo' => 'Order001',
                'AllowanceAmt' => 630,
                'RemainAmt' => 420,
            ]),
        ], 200),
    ]);

    $result = EzPayInvoice::allowance()
        ->create()
        ->withInvoice('GG72002018')
        ->withOrder('Order001')
        ->withItem('退貨商品', quantity: 2, unit: '個', price: 300, amount: 600, taxAmount: 30)
        ->withTotalAmount(630)
        ->withNotification('customer@example.com')
        ->onPreparedOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'RespondType' => 'JSON',
                'Version' => '1.3',
                'TimeStamp' => Carbon::now()->timestamp,
                'InvoiceNo' => 'GG72002018',
                'MerchantOrderNo' => 'Order001',
                'ItemName' => '退貨商品',
                'ItemCount' => '2',
                'ItemUnit' => '個',
                'ItemPrice' => '300',
                'ItemAmt' => '600',
                'ItemTaxAmt' => '30',
                'TotalAmt' => '630',
                'BuyerEmail' => 'customer@example.com',
                'Status' => '1',
            ]);
        })
        ->issue();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api/allowance_issue';
    });

    expect($result)->toBeInstanceOf(CreateResult::class)
        ->and($result->checkCode())->toBe('123456789')
        ->and($result->allowanceNo())->toBe('A250725235346456')
        ->and($result->orderNo())->toBe('Order001')
        ->and($result->invoiceNumber())->toBe('GG72002018')
        ->and($result->allowanceAmount())->toBe(630)
        ->and($result->remainingAmount())->toBe(1050 - 630);
});

test('折讓開立 → 可以開立多品項折讓', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptByAES')->andReturn('encrypted_data');

    Http::fake([
        '*' => Http::response([
            'Status' => 'SUCCESS',
            'Message' => '發票折讓開立成功',
            'Result' => json_encode([
                'CheckCode' => '123456789',
                'AllowanceNo' => 'A250725235346456',
                'InvoiceNumber' => 'GG72002018',
                'MerchantID' => '111335678',
                'MerchantOrderNo' => 'Order001',
                'AllowanceAmt' => 157,
                'RemainAmt' => 0,
            ]),
        ], 200),
    ]);

    $result = EzPayInvoice::allowance()
        ->create()
        ->withInvoice('GG72002018')
        ->withOrder('Order001')
        ->withItem('商品A', quantity: 1, unit: '個', price: 100, amount: 100, taxAmount: 5)
        ->withItem('商品B', quantity: 1, unit: '個', price: 50, amount: 50, taxAmount: 2)
        ->withTotalAmount(157)
        ->withNotification('customer@example.com')
        ->onPreparedOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'RespondType' => 'JSON',
                'Version' => '1.3',
                'TimeStamp' => Carbon::now()->timestamp,
                'InvoiceNo' => 'GG72002018',
                'MerchantOrderNo' => 'Order001',
                'ItemName' => '商品A|商品B',
                'ItemCount' => '1|1',
                'ItemUnit' => '個|個',
                'ItemPrice' => '100|50',
                'ItemAmt' => '100|50',
                'ItemTaxAmt' => '5|2',
                'TotalAmt' => '157',
                'BuyerEmail' => 'customer@example.com',
                'Status' => '1',
            ]);
        })
        ->issue();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api/allowance_issue';
    });

    expect($result)->toBeInstanceOf(CreateResult::class);
});

test('折讓開立 → 可以開立非立即確認的折讓', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptByAES')->andReturn('encrypted_data');

    Http::fake([
        '*' => Http::response([
            'Status' => 'SUCCESS',
            'Message' => '發票折讓開立成功',
            'Result' => json_encode([
                'CheckCode' => '123456789',
                'AllowanceNo' => 'A250726001830959',
                'InvoiceNumber' => 'GG72002018',
                'MerchantID' => '111335678',
                'MerchantOrderNo' => 'Order001',
                'AllowanceAmt' => 420,
                'RemainAmt' => 0,
            ]),
        ], 200),
    ]);

    $result = EzPayInvoice::allowance()
        ->create()
        ->withInvoice('GG72002018')
        ->withOrder('Order001')
        ->withItem('退貨商品', quantity: 2, unit: '個', price: 300, amount: 600, taxAmount: 30)
        ->withTotalAmount(630)
        ->onPreparedOptions(function (Options $options) {
            expect($options->toArray()['PostData_'])->toBe([
                'RespondType' => 'JSON',
                'Version' => '1.3',
                'TimeStamp' => Carbon::now()->timestamp,
                'InvoiceNo' => 'GG72002018',
                'MerchantOrderNo' => 'Order001',
                'ItemName' => '退貨商品',
                'ItemCount' => '2',
                'ItemUnit' => '個',
                'ItemPrice' => '300',
                'ItemAmt' => '600',
                'ItemTaxAmt' => '30',
                'TotalAmt' => '630',
                'Status' => '0',
            ]);
        })
        ->issuePendingConfirmation();

    Http::assertSent(function (Request $request) {
        return $request->url() == 'https://cinv.ezpay.com.tw/Api/allowance_issue';
    });

    expect($result)->toBeInstanceOf(CreateResult::class);
});

test('折讓開立 → 模擬成功開立折讓', function () {
    EzPayInvoice::fake([
        CreateResult::make([
            'Status' => 'SUCCESS',
            'Message' => '發票折讓開立成功',
            'Result' => [
                'CheckCode' => '123456789',
                'AllowanceNo' => 'A250725235346456',
                'InvoiceNumber' => 'GG72002018',
                'MerchantID' => '111335678',
                'MerchantOrderNo' => 'Order001',
                'AllowanceAmt' => 630,
                'RemainAmt' => 420,
            ],
        ]),
    ]);

    $result = EzPayInvoice::allowance()
        ->create()
        ->withInvoice('GG72002018')
        ->withOrder('Order001')
        ->withItem('退貨商品', quantity: 2, unit: '個', price: 300, amount: 600, taxAmount: 30)
        ->withTotalAmount(630)
        ->withNotification('customer@example.com')
        ->issue();

    EzPayInvoice::assertSent(Allowance::class, 'create', function (CreateOptions $options) {
        return $options->invoiceNo === 'GG72002018'
            && $options->orderNo === 'Order001'
            && $options->hasItem('退貨商品', quantity: 2, unit: '個', price: 300, amount: 600, taxAmount: 30)
            && $options->totalAmount === 630
            && $options->buyerEmail === 'customer@example.com'
            && $options->status === CreateStatus::IMMEDIATE;
    });

    expect($result)->toBeInstanceOf(CreateResult::class)
        ->and($result->checkCode())->toBe('123456789')
        ->and($result->allowanceNo())->toBe('A250725235346456')
        ->and($result->orderNo())->toBe('Order001')
        ->and($result->invoiceNumber())->toBe('GG72002018')
        ->and($result->allowanceAmount())->toBe(630)
        ->and($result->remainingAmount())->toBe(1050 - 630);
});

test('折讓開立 → 模擬開立多品項折讓', function () {
    EzPayInvoice::fake([
        CreateResult::make([
            'Status' => 'SUCCESS',
            'Message' => '發票折讓開立成功',
            'Result' => [
                'CheckCode' => '123456789',
                'AllowanceNo' => 'A250725235346456',
                'InvoiceNumber' => 'GG72002018',
                'MerchantID' => '111335678',
                'MerchantOrderNo' => 'Order001',
                'AllowanceAmt' => 157,
                'RemainAmt' => 0,
            ],
        ]),
    ]);

    $result = EzPayInvoice::allowance()
        ->create()
        ->withInvoice('GG72002018')
        ->withOrder('Order001')
        ->withItem('商品A', quantity: 1, unit: '個', price: 100, amount: 100, taxAmount: 5)
        ->withItem('商品B', quantity: 1, unit: '個', price: 50, amount: 50, taxAmount: 2)
        ->withTotalAmount(157)
        ->withNotification('customer@example.com')
        ->issue();

    EzPayInvoice::assertSent(Allowance::class, 'create', function (CreateOptions $options) {
        return $options->invoiceNo === 'GG72002018'
            && $options->orderNo === 'Order001'
            && $options->hasItem('商品A', quantity: 1, unit: '個', price: 100, amount: 100, taxAmount: 5)
            && $options->hasItem('商品B', quantity: 1, unit: '個', price: 50, amount: 50, taxAmount: 2)
            && $options->totalAmount === 157
            && $options->buyerEmail === 'customer@example.com'
            && $options->status === CreateStatus::IMMEDIATE;
    });

    expect($result)->toBeInstanceOf(CreateResult::class);
});

test('折讓開立 → 模擬開立非立即確認的折讓', function () {
    EzPayInvoice::fake([
        CreateResult::make([
            'Status' => 'SUCCESS',
            'Message' => '發票折讓開立成功',
            'Result' => [
                'CheckCode' => '123456789',
                'AllowanceNo' => 'A250726001830959',
                'InvoiceNumber' => 'GG72002018',
                'MerchantID' => '111335678',
                'MerchantOrderNo' => 'Order001',
                'AllowanceAmt' => 420,
                'RemainAmt' => 0,
            ],
        ]),
    ]);

    $result = EzPayInvoice::allowance()
        ->create()
        ->withInvoice('GG72002018')
        ->withOrder('Order001')
        ->withItem('退貨商品', quantity: 2, unit: '個', price: 300, amount: 600, taxAmount: 30)
        ->withTotalAmount(630)
        ->issuePendingConfirmation();

    EzPayInvoice::assertSent(Allowance::class, 'create', function (CreateOptions $options) {
        return $options->invoiceNo === 'GG72002018'
            && $options->orderNo === 'Order001'
            && $options->hasItem('退貨商品', quantity: 2, unit: '個', price: 300, amount: 600, taxAmount: 30)
            && $options->totalAmount === 630
            && $options->status === CreateStatus::PENDING;
    });

    expect($result)->toBeInstanceOf(CreateResult::class);
});
