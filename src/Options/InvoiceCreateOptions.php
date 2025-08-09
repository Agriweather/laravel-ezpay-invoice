<?php

namespace Agriweather\EzpayInvoice\Options;

use Agriweather\EzpayInvoice\Enums\CarrierType;
use Agriweather\EzpayInvoice\Enums\CustomsClearance;
use Agriweather\EzpayInvoice\Enums\InvoiceCategory;
use Agriweather\EzpayInvoice\Enums\InvoiceCreateStatus;
use Agriweather\EzpayInvoice\Enums\InvoicePrintFlag;
use Agriweather\EzpayInvoice\Enums\ItemTaxType;
use Agriweather\EzpayInvoice\Enums\TaxType;
use Carbon\Carbon;

class InvoiceCreateOptions extends Options
{
    public string $merchantId = '';

    public ?string $ezPayTransNumber = null;

    public string $orderNo = '';

    public InvoiceCreateStatus $status = InvoiceCreateStatus::IMMEDIATE;

    public ?string $createDate = null;

    public InvoiceCategory $category = InvoiceCategory::B2C;

    public string $buyerName = '';

    public ?string $buyerTaxIdNumber = null;

    public ?string $buyerAddress = null;

    public ?string $buyerEmail = null;

    public ?CarrierType $carrierType = null;

    public ?string $carrierNumber = null;

    public ?string $loveCode = null;

    public InvoicePrintFlag $printFlag = InvoicePrintFlag::YES;

    public ?bool $enableKioskPrint = null;

    public TaxType $taxType = TaxType::TAXABLE;

    public float $taxRate = 0.0;

    public ?CustomsClearance $customsClearance = null;

    public int $amount = 0;

    public ?int $salesAmount = null;

    public ?int $zeroTaxAmount = null;

    public ?int $freeTaxAmount = null;

    public int $taxAmount = 0;

    public int $totalAmount = 0;

    /** @var string[] */
    public array $itemNames = [];

    /** @var int[] */
    public array $itemQuantities = [];

    /** @var string[] */
    public array $itemUnits = [];

    /** @var int[] */
    public array $itemPrices = [];

    /** @var int[] */
    public array $itemAmounts = [];

    /** @var \Agriweather\EzpayInvoice\Enums\ItemTaxType[]|null */
    public ?array $itemTaxTypes = null;

    public ?string $comment = null;

    /**
     * 檢查是否存在商品項目。
     */
    public function hasItem(string $name, int $quantity, string $unit, int $price, int $amount, ?TaxType $taxType = null): bool
    {
        if (in_array($name, $this->itemNames)) {
            $index = array_search($name, $this->itemNames);

            if ($index === false) {
                return false;
            }

            if (((float) $this->itemQuantities[$index]) === ((float) $quantity) &&
                $this->itemUnits[$index] === $unit &&
                ((float) $this->itemPrices[$index]) === ((float) $price) &&
                ((float) $this->itemAmounts[$index]) === ((float) $amount) &&
                (is_null($taxType) || ($this->itemTaxTypes[$index] ?? null) === $taxType)
            ) {
                return true;
            }
        }

        return false;
    }

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
                    ? implode('|', array_map(fn (ItemTaxType $type) => (string) $type->value, $this->itemTaxTypes))
                    : null,
                'Comment' => $this->comment,
            ], fn ($value) => ! is_null($value)),
        ];
    }
}
