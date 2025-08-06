<?php

namespace Agriweather\EzpayInvoice\Builders;

use Agriweather\EzpayInvoice\Options\CheckBarcodeOptions;
use Agriweather\EzpayInvoice\Results\CheckBarcodeResult;

class CheckBarcodeBuilder extends Builder
{
    protected CheckBarcodeOptions $options;

    protected function boot(): void
    {
        $this->crypto->setHashKey($this->factory->config('merchant_hash_key'));
        $this->crypto->setHashIv($this->factory->config('merchant_hash_iv'));

        $this->options = new CheckBarcodeOptions;
        $this->options->merchantId = $this->factory->config('merchant_id');
    }

    public function getOptions(): CheckBarcodeOptions
    {
        return $this->options;
    }

    public function toRequestData(): array
    {
        $requestData = parent::toRequestData();

        // 如果有 CheckValue 則進行編碼
        if (isset($requestData['formData']['CheckValue']) &&
            is_string($requestData['formData']['PostData_'] ?? null)
        ) {
            $requestData['formData']['CheckValue'] = $this->crypto->encodeCheckValue(
                $requestData['formData']['PostData_']
            );
        }

        return $requestData;
    }

    /**
     * 驗證手機條碼
     *
     * @param  string  $barcode  手機條碼載具，第1碼為 / + 7碼英、數字
     *
     * @throws \Agriweather\EzpayInvoice\Exceptions\EzpayInvoiceException
     * @throws \Agriweather\EzpayInvoice\Exceptions\DecryptException
     */
    public function check(string $barcode): CheckBarcodeResult
    {
        $this->endpoint = '/Api_inv_application/checkBarCode';

        $this->options->barcode = $barcode;

        $response = $this->sendRequest();

        $response['Result'] = $this->crypto->decryptPostData($response['Result']);

        return new CheckBarcodeResult($response);
    }
}
