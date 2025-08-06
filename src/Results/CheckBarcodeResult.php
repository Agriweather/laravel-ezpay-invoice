<?php

namespace Agriweather\EzpayInvoice\Results;

final class CheckBarcodeResult extends Result
{
    /**
     * 手機條碼
     */
    public function barcode(): string
    {
        return $this->result['PhoneBarcode'];
    }

    /**
     * 手機條碼是否存在於財政部電子發票整合服務平台
     */
    public function isValid(): bool
    {
        return $this->result['IsExist'] === 'Y';
    }
}
