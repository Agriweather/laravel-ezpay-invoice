<?php

use Agriweather\EzpayInvoice\Exceptions\EzpayInvoiceException;

describe('EzpayInvoiceException', function () {
    test('可以拋出 ezPay 錯誤', function () {
        $apiUrl = 'https://api.ezpay.com.tw/invoice';
        $formData = ['PostData_' => ''];
        $status = 'KEY10013';
        $message = '資料不可空白MerchantOrderNo';

        throw new EzpayInvoiceException($apiUrl, $formData, $status, $message);
    })->throws(
        EzpayInvoiceException::class,
        'ezPay 發票平台 API 回應錯誤 (Code: KEY10013)：「資料不可空白MerchantOrderNo」'
    );
});
