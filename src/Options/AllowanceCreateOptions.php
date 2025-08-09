<?php

namespace Agriweather\EzPayInvoice\Options;

use Agriweather\EzPayInvoice\Enums\AllowanceCreateStatus;
use Agriweather\EzPayInvoice\Enums\ItemTaxType;
use Carbon\Carbon;

class AllowanceCreateOptions extends Options
{
    public string $merchantId = '';

    public string $invoiceNo = '';

    public string $orderNo = '';

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

    public ?ItemTaxType $taxTypeForMixed = null;

    /** @var int[] */
    public array $itemTaxAmounts = [];

    public int $totalAmount = 0;

    public ?string $buyerEmail = null;

    public AllowanceCreateStatus $status = AllowanceCreateStatus::IMMEDIATE;

    /**
     * 檢查是否存在商品項目。
     */
    public function hasItem(string $name, int $quantity, string $unit, int $price, int $amount, int $taxAmount): bool
    {
        if (in_array($name, $this->itemNames)) {
            $index = array_search($name, $this->itemNames);

            if ($index === false) {
                return false;
            }

            if ($this->itemQuantities[$index] === $quantity &&
                $this->itemUnits[$index] === $unit &&
                $this->itemPrices[$index] === $price &&
                $this->itemAmounts[$index] === $amount &&
                $this->itemTaxAmounts[$index] === $taxAmount
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
                'ItemTaxAmt' => implode('|', $this->itemTaxAmounts),
                'TotalAmt' => (string) $this->totalAmount,
                'BuyerEmail' => $this->buyerEmail,
                'Status' => (string) $this->status->value,
            ], fn ($value) => ! is_null($value)),
        ];
    }
}
