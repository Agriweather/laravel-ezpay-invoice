<?php

use Agriweather\EzPayInvoice\Exceptions\EzPayInvoiceException;

test('可以拋出 EzPayInvoiceException', function () {
    $apiUrl = 'https://cinv.ezpay.com.tw/Api/invoice_issue';
    $formData = ['PostData_' => ''];
    $status = 'KEY10013';
    $message = '資料不可空白MerchantOrderNo';

    throw new EzPayInvoiceException($apiUrl, $formData, $status, $message);
})->throws(
    EzPayInvoiceException::class,
    'ezPay 發票平台 API 回應錯誤 (Code: KEY10013)：「資料不可空白MerchantOrderNo」'
);
