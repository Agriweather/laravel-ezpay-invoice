<?php

namespace Agriweather\EzPayInvoice\Builders\Concerns;

use Closure;

trait HasPrepareOptions
{
    protected ?Closure $onPrepareOptionsCallback = null;

    public function onPrepareOptions(Closure $callback): static
    {
        $this->onPrepareOptionsCallback = $callback;

        return $this;
    }
}
