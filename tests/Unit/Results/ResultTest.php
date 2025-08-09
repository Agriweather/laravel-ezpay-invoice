<?php

use Agriweather\EzPayInvoice\Results\Result;

test('可以解析成功結果', function () {
    $result = new UnitTestResult([
        'Status' => 'SUCCESS',
        'Message' => '發票開立成功',
        'Result' => json_encode([
            'CheckCode' => '123456789',
            'MerchantID' => '111335678',
            'MerchantOrderNo' => 'Order001',
            'InvoiceNumber' => 'GG72002017',
        ]),
    ]);

    expect($result->result())->toBe([
        'CheckCode' => '123456789',
        'MerchantID' => '111335678',
        'MerchantOrderNo' => 'Order001',
        'InvoiceNumber' => 'GG72002017',
    ]);
});

class UnitTestResult extends Result
{
    //
}
