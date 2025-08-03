<?php

namespace Agriweather\EzpayInvoice\Options\Concerns;

use Agriweather\EzpayInvoice\Enums\CustomsClearance;
use Agriweather\EzpayInvoice\Enums\TaxType;

trait HasTax
{
    public TaxType $taxType = TaxType::TAXABLE;

    public int $taxRate = 0;

    public ?CustomsClearance $customsClearance = null;

    public ?int $salesAmount = null;

    public ?int $zeroTaxAmount = null;

    public ?int $freeTaxAmount = null;
}
