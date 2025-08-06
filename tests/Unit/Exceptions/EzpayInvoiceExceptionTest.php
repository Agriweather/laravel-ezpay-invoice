<?php

use Agriweather\EzpayInvoice\Exceptions\EzpayInvoiceException;

describe('EzpayInvoiceException', function () {
    test('可以拋出 ezPay 錯誤', function () {
        $apiUrl = 'https://api.ezpay.com.tw/invoice';
        $formData = ['PostData_' => 'test data'];
        $status = 'AB0001';
        $message = 'API 請求錯誤';

        throw new EzpayInvoiceException($apiUrl, $formData, $status, $message);
    })->throws(EzpayInvoiceException::class, 'ezPay 發票平台 API 回應錯誤 (Code: AB0001)：「API 請求錯誤」');
});
