<?php

namespace Agriweather\EzPayInvoice\Options\CrossBorderInvoice;

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
            'PostData_' => array_filter([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'InvoiceNumber' => $this->invoiceNumber,
                'InvalidReason' => $this->invalidReason,
            ], fn ($value) => ! is_null($value)),
        ];
    }
}
