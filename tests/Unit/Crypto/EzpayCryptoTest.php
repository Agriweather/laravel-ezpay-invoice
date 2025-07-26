<?php

use Agriweather\EzpayInvoice\Crypto\EzpayCrypto;

describe('EzpayCrypto', function () {
    it('可以建立實例並加入 merchant 金鑰', function () {
        $crypto = new EzpayCrypto();
        $crypto
            ->setMerchantId('TEST_MERCHANT_ID')
            ->setHashKey('TEST_MERCHANT_HASH_KEY')
            ->setHashIv('TEST_MERCHANT_HASH_IV');

        expect($crypto)->toBeInstanceOf(EzpayCrypto::class);
    });

    it('可以建立實例並加入 company 金鑰', function () {
        $crypto = new EzpayCrypto();
        $crypto
            ->setCompanyId('TEST_COMPANY_ID')
            ->setHashKey('TEST_COMPANY_HASH_KEY')
            ->setHashIv('TEST_COMPANY_HASH_IV');

        expect($crypto)->toBeInstanceOf(EzpayCrypto::class);
    });
});
