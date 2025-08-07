<?php

namespace Agriweather\EzpayInvoice\Options;

use Agriweather\EzpayInvoice\Enums\AllowanceCreateStatus;
use Agriweather\EzpayInvoice\Enums\ItemTaxType;
use Carbon\Carbon;

class AllowanceCreateOptions extends Options
{
    public string $merchantId = '';

    public string $invoiceNo = '';

    public string $orderNo = '';

    /** @var string[] */
    public array $itemNames = [];

    /** @var int|float|string[] */
    public array $itemQuantities = [];

    /** @var string[] */
    public array $itemUnits = [];

    /** @var int|float|string[] */
    public array $itemPrices = [];

    /** @var int|float|string[] */
    public array $itemAmounts = [];

    public ?ItemTaxType $taxTypeForMixed = null;

    /** @var int|float|string[] */
    public array $ItemTaxAmounts = [];

    public int $totalAmount = 0;

    public ?string $buyerEmail = null;

    public AllowanceCreateStatus $status = AllowanceCreateStatus::IMMEDIATE;

    /**
     * 檢查是否存在商品項目。
     */
    public function hasItem(
        string $name,
        int|float|string $quantity,
        string $unit,
        int|float|string $price,
        int|float|string $amount,
        int|float|string $taxAmount
    ): bool {
        if (in_array($name, $this->itemNames)) {
            $index = array_search($name, $this->itemNames);

            if (((float) $this->itemQuantities[$index]) === ((float) $quantity) &&
                $this->itemUnits[$index] === $unit &&
                ((float) $this->itemPrices[$index]) === ((float) $price) &&
                ((float) $this->itemAmounts[$index]) === ((float) $amount) &&
                ((float) $this->ItemTaxAmounts[$index]) === ((float) $taxAmount)
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
                'Version' => '1.3',
                'TimeStamp' => Carbon::now()->timestamp,
                'InvoiceNo' => $this->invoiceNo,
                'MerchantOrderNo' => $this->orderNo,
                'ItemName' => implode('|', $this->itemNames),
                'ItemCount' => implode('|', $this->itemQuantities),
                'ItemUnit' => implode('|', $this->itemUnits),
                'ItemPrice' => implode('|', $this->itemPrices),
                'ItemAmt' => implode('|', $this->itemAmounts),
                'TaxTypeForMixed' => isset($this->taxTypeForMixed)
                    ? (string) $this->taxTypeForMixed->value
                    : null,
                'ItemTaxAmt' => implode('|', $this->ItemTaxAmounts),
                'TotalAmt' => (string) $this->totalAmount,
                'BuyerEmail' => $this->buyerEmail,
                'Status' => (string) $this->status->value,
            ], fn ($value) => ! is_null($value)),
        ];
    }
}
