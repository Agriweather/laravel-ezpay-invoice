<?php

namespace Agriweather\EzPayInvoice\Resources\Concerns;

use Agriweather\EzPayInvoice\Builders\Concerns\HasTransformOptions;

trait PrepareBuilder
{
    use HasTransformOptions;

    /**
     * @template TBuilder
     *
     * @param  TBuilder  $builder
     * @return TBuilder
     */
    protected function prepareBuilder($builder)
    {
        if ($this->transformOptionsCallback) {
            $builder = $builder->transformOptions($this->transformOptionsCallback);
        }

        return $builder;
    }
}
