<?php

namespace Agriweather\EzPayInvoice\Options\CrossBorderInvoice;

use Agriweather\EzPayInvoice\Enums\Invoice\CurrencyType;
use Agriweather\EzPayInvoice\Enums\Invoice\InvoiceCreateStatus;
use Agriweather\EzPayInvoice\Options\Options;
use Carbon\Carbon;

final class CreateOptions extends Options
{
    public string $merchantId = '';

    public string $orderNo = '';

    public InvoiceCreateStatus $status = InvoiceCreateStatus::IMMEDIATE;

    public ?string $createDate = null;

    public string $buyerName = '';

    public ?string $buyerAddress = null;

    public string $buyerEmail = '';

    public int|float $amount = 0;

    public int|float $taxAmount = 0;

    public int|float $totalAmount = 0;

    /** @var string[] */
    public array $itemNames = [];

    /** @var int[] */
    public array $itemQuantities = [];

    /** @var string[] */
    public array $itemUnits = [];

    /** @var (int|float)[] */
    public array $itemPrices = [];

    /** @var (int|float)[] */
    public array $itemAmounts = [];

    public ?string $comment = null;

    public CurrencyType $currency = CurrencyType::TWD;

    public int|float $originalCurrencyAmount = 0;

    public float $exchangeRate = 1.0;

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
                'MerchantOrderNo' => $this->orderNo,
                'Status' => (string) $this->status->value,
                'CreateStatusTime' => $this->createDate,
                'BuyerName' => $this->buyerName,
                'BuyerAddress' => $this->buyerAddress,
                'BuyerEmail' => $this->buyerEmail,
                'Amt' => (string) round($this->amount, 2),
                'TaxAmt' => (string) round($this->taxAmount, 2),
                'TotalAmt' => (string) round($this->totalAmount, 2),
                'ItemName' => implode('|', $this->itemNames),
                'ItemCount' => implode('|', $this->itemQuantities),
                'ItemUnit' => implode('|', $this->itemUnits),
                'ItemPrice' => implode('|', array_map(fn (int|float $price) => (string) round($price, 2), $this->itemPrices)),
                'ItemAmt' => implode('|', array_map(fn (int|float $amount) => (string) round($amount, 2), $this->itemAmounts)),
                'Comment' => $this->comment,
                'Currency' => $this->currency->value,
                'OriginalCurrencyAmount' => (string) round($this->originalCurrencyAmount, 2),
                'ExchangeRate' => (string) round($this->exchangeRate, 5),
            ], fn ($value) => ! is_null($value)),
        ];
    }
}
