<?php

use Agriweather\EzpayInvoice\Crypto\EzpayCrypto;

describe('EzpayCrypto', function () {
    it('可以使用 merchant 金鑰建立實例', function () {
        $crypto = new EzpayCrypto(
            'TEST_MERCHANT_ID',
            'TEST_MERCHANT_HASH_KEY',
            'TEST_MERCHANT_HASH_IV'
        );

        expect($crypto)->toBeInstanceOf(EzpayCrypto::class);
    });

    it('可以使用 company 金鑰建立實例', function () {
        $crypto = new EzpayCrypto(
            'TEST_COMPANY_ID',
            'TEST_COMPANY_HASH_KEY',
            'TEST_COMPANY_HASH_IV'
        );

        expect($crypto)->toBeInstanceOf(EzpayCrypto::class);
    });
});
