<?php

namespace Agriweather\EzpayInvoice\Results\Concerns;

trait HasCheckCode
{
    /**
     * 檢查碼
     */
    public function checkCode(): string
    {
        return $this->result['CheckCode'];
    }
}
