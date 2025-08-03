<?php

namespace Agriweather\EzpayInvoice\Options;

use Carbon\Carbon;

class InvoiceQueryOptions extends Options
{
    public string $merchantId = '';

    public function toArray()
    {
        return [
            'MerchantID_' => $this->merchantId,
            'PostData_' => array_filter([
                'RespondType' => 'JSON',
                'Version' => '1.3',
                'TimeStamp' => Carbon::now()->timestamp,
            ], fn ($value) => ! is_null($value)),
        ];
    }
}
