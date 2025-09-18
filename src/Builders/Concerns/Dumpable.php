<?php

namespace Agriweather\EzPayInvoice\Builders\Concerns;

/**
 * @method \Agriweather\EzPayInvoice\Options\Options options()
 */
trait Dumpable
{
    /**
     * Die and dump the current options.
     */
    public function dd(): never
    {
        dd($this->options()->toArray());
    }
}
