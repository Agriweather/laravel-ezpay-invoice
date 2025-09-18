<?php

namespace Agriweather\EzPayInvoice\Builders\Concerns;

trait HasTransformOptions
{
    /**
     * @var callable|null
     */
    protected $transformOptionsCallback;

    /**
     * @param  callable  $callback
     * @return $this
     */
    public function transformOptions($callback)
    {
        $this->transformOptionsCallback = $callback;

        return $this;
    }
}
