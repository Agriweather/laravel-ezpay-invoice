<?php

namespace Agriweather\EzpayInvoice\Options;

use Carbon\Carbon;

class CheckLoveCodeOptions extends Options
{
    public string $merchantId = '';

    public string $lovecode = '';

    public function toArray()
    {
        return [
            'MerchantID_' => $this->merchantId,
            'Version' => '1.0',
            'RespondType' => 'JSON',
            'PostData_' => array_filter([
                'TimeStamp' => Carbon::now()->timestamp,
                'LoveCode' => $this->lovecode,
            ], fn ($value) => ! is_null($value)),
            'CheckValue' => '', // 在 CheckLoveCodeBuilder 中會進行編碼處理
        ];
    }
}
