<?php

namespace Agriweather\EzpayInvoice\Results\Concerns;

trait HasOrderNo
{
    /**
     * 商店自訂訂單編號
     */
    public function orderNo(): string
    {
        return $this->result['MerchantOrderNo'];
    }
}
