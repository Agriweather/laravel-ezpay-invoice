<?php

namespace Agriweather\EzpayInvoice\Results\Concerns;

use Carbon\Carbon;

trait HasCreateTime
{
    /**
     * 開立發票時間
     *
     * @throws \Carbon\Exceptions\InvalidFormatException
     */
    public function createTime(): ?Carbon
    {
        if ($createTime = $this->result['CreateTime']) {
            return Carbon::createFromFormat('Y-m-d H:i:s', $createTime);
        }

        return null;
    }
}
