<?php

namespace Agriweather\EzPayInvoice;

use Agriweather\EzPayInvoice\Builders\Concerns\HasTransformOptions;

class SubFactory
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
