<?php

namespace Agriweather\EzPayInvoice\Results\Concerns;

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
