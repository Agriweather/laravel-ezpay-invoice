<?php

namespace Agriweather\EzPayInvoice\Options\Allowance;

use Agriweather\EzPayInvoice\Enums\Allowance\TriggerStatus;
use Agriweather\EzPayInvoice\Options\Options;
use Carbon\Carbon;

final class TriggerOptions extends Options
{
    public string $merchantId = '';

    public string $allowanceNo = '';

    public string $orderNo = '';

    public TriggerStatus $status = TriggerStatus::YES;

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
