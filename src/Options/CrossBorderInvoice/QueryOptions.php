<?php

namespace Agriweather\EzPayInvoice\Options\CrossBorderInvoice;

use Agriweather\EzPayInvoice\Enums\Invoice\DisplayFlag;
use Agriweather\EzPayInvoice\Enums\Invoice\SearchType;
use Agriweather\EzPayInvoice\Options\Options;
use Carbon\Carbon;

final class QueryOptions extends Options
{
    public string $merchantId = '';

    public ?SearchType $searchType = null;

    public string $orderNo = '';

    public int|float $totalAmount = 0;

    public string $invoiceNumber = '';

    public string $randomNumber = '';

    public ?DisplayFlag $displayFlag = null;

    public function toArray()
    {
        return [
            'MerchantID_' => $this->merchantId,
            'PostData_' => array_filter([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'SearchType' => isset($this->searchType)
                    ? (string) $this->searchType->value
                    : null,
                'MerchantOrderNo' => $this->orderNo,
                'TotalAmt' => (string) round($this->totalAmount, 2),
                'InvoiceNumber' => $this->invoiceNumber,
                'RandomNum' => $this->randomNumber,
                'DisplayFlag' => isset($this->displayFlag)
                    ? (string) $this->displayFlag->value
                    : null,
            ], fn ($value) => ! is_null($value)),
        ];
    }
}
