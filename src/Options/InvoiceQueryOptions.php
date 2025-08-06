<?php

namespace Agriweather\EzpayInvoice\Options;

use Agriweather\EzpayInvoice\Enums\DisplayFlag;
use Agriweather\EzpayInvoice\Enums\SearchType;
use Carbon\Carbon;

class InvoiceQueryOptions extends Options
{
    public string $merchantId = '';

    public string $version = '';

    public ?string $orderNo = null;

    public ?string $ezPayTransNumber = null;

    public ?string $invoiceNumber = null;

    public ?string $invoiceTransNumber = null;

    public ?SearchType $searchType = null;

    public ?int $totalAmount = null;

    public ?string $randomNumber = null;

    public ?string $invalidReason = null;

    public ?DisplayFlag $displayFlag = null;

    public function toArray()
    {
        return [
            'MerchantID_' => $this->merchantId,
            'PostData_' => array_filter([
                'RespondType' => 'JSON',
                'Version' => $this->version,
                'TimeStamp' => Carbon::now()->timestamp,
                'MerchantOrderNo' => $this->orderNo,
                'TransNum' => $this->ezPayTransNumber,
                'InvoiceNumber' => $this->invoiceNumber,
                'InvoiceTransNo' => $this->invoiceTransNumber,
                'SearchType' => isset($this->searchType)
                    ? (string) $this->searchType->value
                    : null,
                'TotalAmt' => isset($this->totalAmount)
                    ? (string) $this->totalAmount
                    : null,
                'RandomNum' => $this->randomNumber,
                'InvalidReason' => $this->invalidReason,
                'DisplayFlag' => isset($this->displayFlag)
                    ? (string) $this->displayFlag->value
                    : null,
            ], fn ($value) => ! is_null($value)),
        ];
    }
}
