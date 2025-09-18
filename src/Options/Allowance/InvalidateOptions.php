<?php

namespace Agriweather\EzPayInvoice\Options\Allowance;

use Agriweather\EzPayInvoice\Options\Options;
use Carbon\Carbon;

final class InvalidateOptions extends Options
{
    public string $merchantId = '';

    public string $allowanceNo = '';

    public string $invalidReason = '';

    public function toArray()
    {
        return [
            'MerchantID_' => $this->merchantId,
            'PostData_' => [
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'AllowanceNo' => $this->allowanceNo,
                'InvalidReason' => $this->invalidReason,
            ],
        ];
    }
}
