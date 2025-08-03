<?php

use Agriweather\EzpayInvoice\Crypto\EzpayCrypto;

describe('EzpayCrypto', function () {
    test('可以建立實例並加入金鑰', function () {
        $crypto = new EzpayCrypto();
        $crypto
            ->setHashKey('TEST_MERCHANT_HASH_KEY')
            ->setHashIv('TEST_MERCHANT_HASH_IV');

        expect($crypto)->toBeInstanceOf(EzpayCrypto::class);
    });
});
