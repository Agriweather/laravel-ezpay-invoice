<?php

use Agriweather\EzPayInvoice\Crypto\Crypto;
use Agriweather\EzPayInvoice\Enums\Invoice\TaxType;
use Agriweather\EzPayInvoice\Exceptions\EzPayInvoiceException;
use Agriweather\EzPayInvoice\Facades\EzPayInvoice;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\partialMock;

test('應該可以拋出 ezPay 錯誤', function () {
    $crypto = partialMock(Crypto::class);
    $crypto->expects('encryptByAES')->andReturn('encrypted_data');
    $crypto->shouldNotReceive('verifyCheckCode');

    Http::fake([
        '*' => Http::response([
            'Status' => 'KEY10013',
            'Message' => '資料不可空白MerchantOrderNo',
            'Result' => json_encode([]),
        ], 200),
    ]);

    EzPayInvoice::invoice()
        ->create()
        ->withOrder('')
        ->forConsumer('John Doe')
        ->withEmail('customer@example.com')
        ->withAddress('台北市信義區信義路五段7號')
        ->withItem('測試商品', quantity: 1, unit: '個', price: 1000, amount: 1000)
        ->withTax(TaxType::TAXABLE, 5)
        ->withAmount(1000, 50, 1050)
        ->issue();
})->throws(
    EzPayInvoiceException::class,
    'ezPay 發票平台 API 回應錯誤 (Code: KEY10013)：「資料不可空白MerchantOrderNo」'
);
