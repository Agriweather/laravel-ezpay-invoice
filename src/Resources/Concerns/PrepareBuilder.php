<?php

namespace Agriweather\EzPayInvoice\Resources\Concerns;

use Agriweather\EzPayInvoice\Builders\Concerns\HasPrepareOptions;

trait PrepareBuilder
{
    use HasPrepareOptions;

    /**
     * @template TBuilder
     *
     * @param  TBuilder  $builder
     * @return TBuilder
     */
    protected function prepareBuilder($builder)
    {
        if ($this->onPreparedOptionsCallback) {
            return $builder->onPreparedOptions($this->onPreparedOptionsCallback);
        }

        return $builder;
    }
}
