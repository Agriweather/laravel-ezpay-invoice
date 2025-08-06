<?php

namespace Agriweather\EzpayInvoice\Results\Concerns;

trait HasRandomNumber
{
    /**
     * 發票防偽隨機碼
     */
    public function randomNumber(): string
    {
        return $this->result['RandomNum'];
    }
}
