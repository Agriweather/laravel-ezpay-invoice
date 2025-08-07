<?php

namespace Agriweather\EzpayInvoice\Builders\Concerns;

trait HasTransformOptions
{
    /**
     * @var callable|null
     */
    protected $transformOptionsCallback = null;

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
