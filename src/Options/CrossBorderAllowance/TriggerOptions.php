<?php

namespace Agriweather\EzPayInvoice\Options\CrossBorderAllowance;

use Agriweather\EzPayInvoice\Enums\Allowance\TriggerStatus;
use Agriweather\EzPayInvoice\Options\Options;
use Carbon\Carbon;

class TriggerOptions extends Options
{
    public string $merchantId = '';

    public string $allowanceNo = '';

    public string $orderNo = '';

    public TriggerStatus $status = TriggerStatus::YES;

    public int|float $totalAmount = 0;

    public function toArray()
    {
        return [
            'MerchantID_' => $this->merchantId,
            'PostData_' => array_filter([
                'RespondType' => 'JSON',
                'Version' => '1.3',
                'TimeStamp' => Carbon::now()->timestamp,
                'AllowanceStatus' => $this->status->value,
                'AllowanceNo' => $this->allowanceNo,
                'MerchantOrderNo' => $this->orderNo,
                'TotalAmt' => (string) round($this->totalAmount, 2),
            ], fn ($value) => ! is_null($value)),
        ];
    }
}
