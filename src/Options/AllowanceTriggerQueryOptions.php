<?php

namespace Agriweather\EzpayInvoice\Options;

use Agriweather\EzpayInvoice\Enums\AllowanceTriggerStatus;
use Carbon\Carbon;

class AllowanceTriggerQueryOptions extends Options
{
    public string $merchantId = '';

    public string $allowanceNo = '';

    public string $orderNo = '';

    public AllowanceTriggerStatus $status = AllowanceTriggerStatus::YES;

    public int $totalAmount = 0;

    public function toArray()
    {
        return [
            'MerchantID_' => $this->merchantId,
            'PostData_' => array_filter([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'AllowanceStatus' => $this->status->value,
                'AllowanceNo' => $this->allowanceNo,
                'MerchantOrderNo' => $this->orderNo,
                'TotalAmt' => (string) $this->totalAmount,
            ], fn ($value) => ! is_null($value)),
        ];
    }
}
