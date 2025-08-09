<?php

namespace Agriweather\EzPayInvoice\Results\Concerns;

trait HasInvoiceNumber
{
    /**
     * 發票號碼
     */
    public function invoiceNumber(): ?string
    {
        return $this->result['InvoiceNumber'] ?: null;
    }
}
