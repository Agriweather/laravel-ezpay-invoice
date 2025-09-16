<?php

namespace Agriweather\EzPayInvoice\Options\Invoice;

use Agriweather\EzPayInvoice\Options\Options;
use Carbon\Carbon;

final class TriggerOptions extends Options
{
    public string $merchantId = '';

    public ?string $ezPayTransNumber = null;

    public string $invoiceTransNo = '';

    public string $orderNo = '';

    public int $totalAmount = 0;

    public function toArray()
    {
        return [
            'MerchantID_' => $this->merchantId,
            'PostData_' => array_filter([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'TransNum' => $this->ezPayTransNumber,
                'InvoiceTransNo' => $this->invoiceTransNo,
                'MerchantOrderNo' => $this->orderNo,
                'TotalAmt' => (string) $this->totalAmount,
            ], fn ($value) => ! is_null($value)),
        ];
    }
}
