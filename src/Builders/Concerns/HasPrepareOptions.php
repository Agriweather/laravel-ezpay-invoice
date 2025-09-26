<?php

namespace Agriweather\EzPayInvoice\Builders\Concerns;

use Closure;

trait HasPrepareOptions
{
    protected ?Closure $onPreparedOptionsCallback = null;

    public function onPreparedOptions(Closure $callback): static
    {
        $this->onPreparedOptionsCallback = $callback;

        return $this;
    }
}
