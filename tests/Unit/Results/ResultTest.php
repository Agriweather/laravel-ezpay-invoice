<?php

use Agriweather\EzpayInvoice\Results\Result;

describe('Result', function () {
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

        expect($result->isSuccess())->toBeTrue();
    });

    test('可以解析失敗結果', function () {
        $result = new UnitTestResult([
            'Status' => 'KEY10002',
            'Message' => '資料解密錯誤',
            'Result' => json_encode([]),
        ]);

        expect($result->isSuccess())->toBeFalse();
        expect($result->errorCode())->toBe('KEY10002');
        expect($result->errorMessage())->toBe('資料解密錯誤');
    });
});

class UnitTestResult extends Result
{
    //
}
