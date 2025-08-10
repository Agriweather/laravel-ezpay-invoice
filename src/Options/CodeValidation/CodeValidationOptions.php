<?php

namespace Agriweather\EzPayInvoice\Options\CodeValidation;

use Agriweather\EzPayInvoice\Options\Options;
use Carbon\Carbon;

class CodeValidationOptions extends Options
{
    public string $merchantId = '';

    public ?string $barcode = null;

    public ?string $lovecode = null;

    public function toArray()
    {
        return [
            'MerchantID_' => $this->merchantId,
            'Version' => '1.0',
            'RespondType' => 'JSON',
            'PostData_' => array_filter([
                'TimeStamp' => Carbon::now()->timestamp,
                'CellphoneBarcode' => $this->barcode,
                'LoveCode' => $this->lovecode,
            ], fn ($value) => ! is_null($value)),
            'CheckValue' => '', // 在 CodeValidationBuilder 中會進行編碼處理
        ];
    }
}
