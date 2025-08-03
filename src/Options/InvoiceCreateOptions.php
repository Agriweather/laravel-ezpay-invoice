<?php

namespace Agriweather\EzpayInvoice\Options;

use Agriweather\EzpayInvoice\Enums\InvoiceCategory;
use Agriweather\EzpayInvoice\Enums\InvoiceStatus;
use Agriweather\EzpayInvoice\Enums\TaxType;
use Carbon\Carbon;

class InvoiceCreateOptions extends Options
{
    use Concerns\HasAmount;
    use Concerns\HasBuyer;
    use Concerns\HasCarrier;
    use Concerns\HasItems;
    use Concerns\HasTax;

    public string $merchantId = '';

    public ?string $ezPayTransNumber = null;

    public string $orderNo = '';

    public InvoiceStatus $status = InvoiceStatus::IMMEDIATE;

    public ?string $createDate = null;

    public InvoiceCategory $category = InvoiceCategory::B2C;

    public ?string $comment = null;

    public function toArray()
    {
        return [
            'MerchantID_' => $this->merchantId,
            'PostData_' => array_filter([
                'RespondType' => 'JSON',
                'Version' => '1.5',
                'TimeStamp' => Carbon::now()->timestamp,
                'TransNum' => $this->ezPayTransNumber,
                'MerchantOrderNo' => $this->orderNo,
                'Status' => (string) $this->status->value,
                'CreateStatusTime' => $this->createDate,
                'Category' => (string) $this->category->value,
                'BuyerName' => $this->buyerName,
                'BuyerUBN' => $this->buyerTaxIdNumber,
                'BuyerAddress' => $this->buyerAddress,
                'BuyerEmail' => $this->buyerEmail,
                'CarrierType' => isset($this->carrierType)
                    ? (string) $this->carrierType->value
                    : null,
                'CarrierNum' => $this->carrierNumber,
                'LoveCode' => $this->loveCode,
                'PrintFlag' => (string) $this->printFlag->value,
                'KioskPrintFlag' => $this->enableKioskPrint ? '1' : null,
                'TaxType' => (string) $this->taxType->value,
                'TaxRate' => (string) $this->taxRate,
                'CustomsClearance' => isset($this->customsClearance)
                    ? (string) $this->customsClearance->value
                    : null,
                'Amt' => (string) $this->amount,
                'AmtSales' => isset($this->salesAmount)
                    ? (string) $this->salesAmount
                    : null,
                'AmtZero' => isset($this->zeroTaxAmount)
                    ? (string) $this->zeroTaxAmount
                    : null,
                'AmtFree' => isset($this->freeTaxAmount)
                    ? (string) $this->freeTaxAmount
                    : null,
                'TaxAmt' => (string) $this->taxAmount,
                'TotalAmt' => (string) $this->totalAmount,
                'ItemName' => implode('|', $this->itemNames),
                'ItemCount' => implode('|', $this->itemQuantities),
                'ItemUnit' => implode('|', $this->itemUnits),
                'ItemPrice' => implode('|', $this->itemPrices),
                'ItemAmt' => implode('|', $this->itemAmounts),
                'ItemTaxType' => is_array($this->itemTaxTypes)
                    ? implode('|', array_map(function (TaxType $type) {
                        return (string) $type->value;
                    }, $this->itemTaxTypes))
                    : null,
                'Comment' => $this->comment,
            ], fn ($value) => ! is_null($value)),
        ];
    }
}
