<?php

namespace Agriweather\EzpayInvoice\Results;

use Carbon\Carbon;

final class AllowanceInvalidateResult extends Result
{
    use Concerns\HasCheckCode;
    use Concerns\HasMerchantID;

    /**
     * 折讓號
     */
    public function allowanceNo(): string
    {
        return $this->result['AllowanceNo'];
    }

    /**
     * 作廢折讓時間
     *
     * @throws \Carbon\Exceptions\InvalidFormatException
     */
    public function createTime(): Carbon
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->result['CreateTime']);
    }
}
