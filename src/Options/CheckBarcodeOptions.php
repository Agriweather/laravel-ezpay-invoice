<?php

namespace Agriweather\EzpayInvoice\Options;

use Carbon\Carbon;

class CheckBarcodeOptions extends Options
{
    public string $merchantId = '';

    public string $barcode = '';

    public function toArray()
    {
        return [
            'MerchantID_' => $this->merchantId,
            'Version' => '1.0',
            'RespondType' => 'JSON',
            'PostData_' => array_filter([
                'TimeStamp' => Carbon::now()->timestamp,
                'CellphoneBarcode' => $this->barcode,
            ], fn ($value) => ! is_null($value)),
            'CheckValue' => '', // 在 CheckBarcodeBuilder 中會進行編碼處理
        ];
    }
}
