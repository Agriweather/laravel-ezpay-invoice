<?php

namespace Agriweather\EzpayInvoice\Results;

use Agriweather\EzpayInvoice\Contracts\CheckCodeVerifiable;
use Carbon\Carbon;

final class InvoiceCreateResult extends Result implements CheckCodeVerifiable
{
    /**
     * 商店代號
     */
    public function merchantID(): string
    {
        return $this->result['MerchantID'];
    }

    /**
     * ezPay 電子發票開立序號
     */
    public function invoiceTransNo(): string
    {
        return $this->result['InvoiceTransNo'];
    }

    /**
     * 商店自訂訂單編號
     */
    public function orderNo(): string
    {
        return $this->result['MerchantOrderNo'];
    }

    /**
     * 發票金額
     */
    public function totalAmount(): int
    {
        return (int) $this->result['TotalAmt'];
    }

    /**
     * 發票號碼
     */
    public function invoiceNumber(): ?string
    {
        return $this->result['InvoiceNumber'] ?: null;
    }

    /**
     * 發票防偽隨機碼
     */
    public function randomNumber(): string
    {
        return $this->result['RandomNum'];
    }

    /**
     * 開立發票時間
     */
    public function createTime(): ?Carbon
    {
        if ($createTime = $this->result['CreateTime']) {
            return Carbon::createFromFormat('Y-m-d H:i:s', $createTime);
        }

        return null;
    }

    /**
     * 檢查碼
     */
    public function checkCode(): string
    {
        return $this->result['CheckCode'];
    }

    /**
     * 發票條碼
     */
    public function barCode(): ?string
    {
        return $this->result['BarCode'];
    }

    /**
     * 發票 QRCode (左)
     */
    public function qrcodeL(): ?string
    {
        return $this->result['QRcodeL'];
    }

    /**
     * 發票 QRCode (右)
     */
    public function qrcodeR(): ?string
    {
        return $this->result['QRcodeR'];
    }
}
