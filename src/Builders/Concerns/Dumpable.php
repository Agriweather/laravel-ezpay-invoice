<?php

namespace Agriweather\EzPayInvoice\Builders\Concerns;

/**
 * @method \Agriweather\EzPayInvoice\Options\Options options()
 */
trait Dumpable
{
    /**
     * Die and dump the current options.
     *
     * @return never
     */
    public function dd()
    {
        dd($this->options()->toArray());
    }
}
