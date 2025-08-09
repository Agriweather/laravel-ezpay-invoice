<?php

use Agriweather\EzPayInvoice\Exceptions\InvalidCheckCodeException;

describe('InvalidCheckCodeException', function () {
    test('可以拋出 checkCode 錯誤', function () {
        throw new InvalidCheckCodeException([]);
    })->throws(InvalidCheckCodeException::class, '驗證檢查碼無效');
});
