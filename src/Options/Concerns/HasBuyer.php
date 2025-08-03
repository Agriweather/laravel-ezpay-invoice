<?php

namespace Agriweather\EzpayInvoice\Options\Concerns;

trait HasBuyer
{
    public string $buyerName = '';

    public ?string $buyerTaxIdNumber = null;

    public ?string $buyerAddress = null;

    public ?string $buyerEmail = null;
}
