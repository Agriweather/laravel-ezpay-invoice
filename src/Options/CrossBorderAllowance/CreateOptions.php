<?php

namespace Agriweather\EzPayInvoice\Options\CrossBorderAllowance;

use Agriweather\EzPayInvoice\Enums\Allowance\CreateStatus;
use Agriweather\EzPayInvoice\Options\Options;
use Carbon\Carbon;

final class CreateOptions extends Options
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

    /** @var int|float[] */
    public array $itemPrices = [];

    /** @var int|float[] */
    public array $itemAmounts = [];

    /** @var int|float[] */
    public array $itemTaxAmounts = [];

    public int|float $totalAmount = 0;

    public ?string $buyerEmail = null;

    public CreateStatus $status = CreateStatus::IMMEDIATE;

    /**
     * 檢查是否存在商品項目。
     */
    public function hasItem(string $name, int $quantity, string $unit, int|float $price, int|float $amount): bool
    {
        if (in_array($name, $this->itemNames)) {
            $index = array_search($name, $this->itemNames);

            if ($index === false) {
                return false;
            }

            if ($this->itemQuantities[$index] === $quantity &&
                $this->itemUnits[$index] === $unit &&
                $this->itemPrices[$index] === $price &&
                $this->itemAmounts[$index] === $amount
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
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'InvoiceNo' => $this->invoiceNo,
                'MerchantOrderNo' => $this->orderNo,
                'ItemName' => implode('|', $this->itemNames),
                'ItemCount' => implode('|', $this->itemQuantities),
                'ItemUnit' => implode('|', $this->itemUnits),
                'ItemPrice' => implode('|', array_map(function (int|float $price) {
                    return (string) round($price, 2);
                }, $this->itemPrices)),
                'ItemAmt' => implode('|', array_map(function (int|float $amount) {
                    return (string) round($amount, 2);
                }, $this->itemAmounts)),
                'ItemTaxAmt' => implode('|', array_map(function (int|float $taxAmount) {
                    return (string) round($taxAmount, 2);
                }, $this->itemTaxAmounts)),
                'TotalAmt' => (string) round($this->totalAmount, 2),
                'BuyerEmail' => $this->buyerEmail,
                'Status' => (string) $this->status->value,
            ], fn ($value) => ! is_null($value)),
        ];
    }
}
