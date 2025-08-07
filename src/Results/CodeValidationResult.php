<?php

namespace Agriweather\EzpayInvoice\Results;

final class CodeValidationResult extends Result
{
    /**
     * 手機條碼
     */
    public function barcode(): ?string
    {
        return $this->result['CellphoneBarcode'] ?? null;
    }

    /**
     * 捐贈碼
     */
    public function loveCode(): ?string
    {
        return $this->result['Lovecode'] ?? null;
    }

    /**
     * 手機條碼是否存在於財政部電子發票整合服務平台
     */
    public function isValid(): bool
    {
        return $this->result['IsExist'] === 'Y';
    }
}
