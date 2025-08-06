<?php

namespace Agriweather\EzpayInvoice\Results;

final class CheckLoveCodeResult extends Result
{
    /**
     * 捐贈碼
     */
    public function loveCode(): string
    {
        return $this->result['Lovecode'];
    }

    /**
     * 捐贈碼是否存在於財政部電子發票整合服務平台
     */
    public function isValid(): bool
    {
        return $this->result['IsExist'] === 'Y';
    }
}
