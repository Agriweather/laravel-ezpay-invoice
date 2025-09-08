<?php

namespace Agriweather\EzPayInvoice\Options\CrossBorderAllowance;

use Agriweather\EzPayInvoice\Options\Options;
use Carbon\Carbon;

class InvalidateOptions extends Options
{
    public string $merchantId = '';

    public string $allowanceNo = '';

    public string $invalidReason = '';

    public function toArray()
    {
        return [
            'MerchantID_' => $this->merchantId,
            'PostData_' => array_filter([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'AllowanceNo' => $this->allowanceNo,
                'InvalidReason' => $this->invalidReason,
            ], fn ($value) => ! is_null($value)),
        ];
    }
}
