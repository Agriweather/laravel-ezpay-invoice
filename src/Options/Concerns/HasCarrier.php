<?php

namespace Agriweather\EzpayInvoice\Options\Concerns;

use Agriweather\EzpayInvoice\Enums\CarrierType;
use Agriweather\EzpayInvoice\Enums\InvoicePrintFlag;

trait HasCarrier
{
    public ?CarrierType $carrierType = null;

    public ?string $carrierNumber = null;

    public ?string $loveCode = null;

    public InvoicePrintFlag $printFlag = InvoicePrintFlag::YES;

    public ?bool $enableKioskPrint = null;
}
