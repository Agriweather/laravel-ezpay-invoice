<?php

namespace Agriweather\EzPayInvoice\Results\AlphanumericCode;

use Agriweather\EzPayInvoice\Results\Concerns;
use Agriweather\EzPayInvoice\Results\Result;

final class QueryResult extends Result
{
    use Concerns\HasAlphanumericCode;
    use Concerns\HasCheckCode;

    /**
     * 使用中的發票號碼
     *
     * 例如：00000101
     */
    public function usedNumber(): string
    {
        return $this->result['UsedNumber'];
    }
}
