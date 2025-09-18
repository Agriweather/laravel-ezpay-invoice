<?php

namespace Agriweather\EzPayInvoice\Options\Invoice;

use Agriweather\EzPayInvoice\Options\Options;
use Carbon\Carbon;

final class InvalidateOptions extends Options
{
    public string $merchantId = '';

    public string $invoiceNumber = '';

    public string $invalidReason = '';

    public function toArray()
    {
        return [
            'MerchantID_' => $this->merchantId,
            'PostData_' => [
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'InvoiceNumber' => $this->invoiceNumber,
                'InvalidReason' => $this->invalidReason,
            ],
        ];
    }
}
