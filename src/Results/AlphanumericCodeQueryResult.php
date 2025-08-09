<?php

namespace Agriweather\EzPayInvoice\Results;

final class AlphanumericCodeQueryResult extends Result
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
