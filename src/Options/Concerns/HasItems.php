<?php

namespace Agriweather\EzpayInvoice\Options\Concerns;

use Agriweather\EzpayInvoice\Enums\TaxType;

trait HasItems
{
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

    /** @var \Agriweather\EzpayInvoice\Enums\TaxType[]|null */
    public ?array $itemTaxTypes = null;

    /**
     * 檢查是否存在商品項目。
     */
    public function hasItem(
        string $name,
        int|float|string $quantity,
        string $unit,
        int|float|string $price,
        int|float|string $amount,
        ?TaxType $taxType = null
    ): bool {
        if (in_array($name, $this->itemNames)) {
            $index = array_search($name, $this->itemNames);

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
}
