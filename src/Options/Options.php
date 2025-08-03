<?php

namespace Agriweather\EzpayInvoice\Options;

use Illuminate\Contracts\Support\Arrayable;

abstract class Options implements Arrayable
{
    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }
}
