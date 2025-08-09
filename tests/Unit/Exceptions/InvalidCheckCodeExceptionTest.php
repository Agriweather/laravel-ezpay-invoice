<?php

use Agriweather\EzPayInvoice\Exceptions\InvalidCheckCodeException;

test('可以拋出 InvalidCheckCodeException', function () {
    throw new InvalidCheckCodeException([]);
})->throws(InvalidCheckCodeException::class, '驗證檢查碼無效');
