<?php

namespace Agriweather\EzpayInvoice\Options\Concerns;

trait HasAmount
{
    public int $amount = 0;

    public int $taxAmount = 0;

    public int $totalAmount = 0;
}
