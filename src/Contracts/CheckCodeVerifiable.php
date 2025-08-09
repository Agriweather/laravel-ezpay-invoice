<?php

namespace Agriweather\EzpayInvoice\Contracts;

interface CheckCodeVerifiable
{
    /**
     * 商店代號
     */
    public function merchantID(): string;

    /**
     * ezPay 電子發票開立序號
     */
    public function invoiceTransNo(): string;

    /**
     * 商店自訂訂單編號
     */
    public function orderNo(): string;

    /**
     * 發票金額
     */
    public function totalAmount(): int|float;

    /**
     * 發票防偽隨機碼
     */
    public function randomNumber(): string;

    /**
     * 檢查碼
     */
    public function checkCode(): string;
}
