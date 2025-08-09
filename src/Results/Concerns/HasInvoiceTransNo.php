<?php

namespace Agriweather\EzPayInvoice\Results\Concerns;

trait HasInvoiceTransNo
{
    /**
     * ezPay 電子發票開立序號
     */
    public function invoiceTransNo(): string
    {
        return $this->result['InvoiceTransNo'];
    }
}
