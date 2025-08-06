<?php

namespace Agriweather\EzpayInvoice\Results\Concerns;

trait HasBarCode
{
    /**
     * 發票條碼
     */
    public function barCode(): ?string
    {
        return $this->result['BarCode'] ?: null;
    }

    /**
     * 發票 QRCode (左)
     */
    public function qrcodeL(): ?string
    {
        return $this->result['QRcodeL'] ?: null;
    }

    /**
     * 發票 QRCode (右)
     */
    public function qrcodeR(): ?string
    {
        return $this->result['QRcodeR'] ?: null;
    }
}
