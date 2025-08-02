<?php

namespace Agriweather\EzpayInvoice\Results;

use Carbon\Carbon;

final class InvoiceCreateResult extends Result
{
    public function merchantID(): string
    {
        return $this->result['MerchantID'];
    }

    public function invoiceTransNo(): string
    {
        return $this->result['InvoiceTransNo'];
    }

    public function orderNo(): string
    {
        return $this->result['MerchantOrderNo'];
    }

    public function totalAmount(): int
    {
        return (int) $this->result['TotalAmt'];
    }

    public function invoiceNumber(): ?string
    {
        return $this->result['InvoiceNumber'] ?: null;
    }

    public function randomNumber(): string
    {
        return $this->result['RandomNum'];
    }

    public function createTime(): ?Carbon
    {
        if ($createTime = $this->result['CreateTime']) {
            return Carbon::createFromFormat('Y-m-d H:i:s', $createTime);
        }

        return null;
    }

    public function checkCode(): string
    {
        return $this->result['CheckCode'];
    }

    public function barCode(): ?string
    {
        return $this->result['BarCode'];
    }

    public function qrcodeL(): ?string
    {
        return $this->result['QRcodeL'];
    }

    public function qrcodeR(): ?string
    {
        return $this->result['QRcodeR'];
    }
}
