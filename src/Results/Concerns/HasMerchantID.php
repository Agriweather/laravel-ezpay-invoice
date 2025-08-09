<?php

namespace Agriweather\EzPayInvoice\Results\Concerns;

trait HasMerchantID
{
    /**
     * 商店代號
     */
    public function merchantID(): string
    {
        return $this->result['MerchantID'];
    }
}
