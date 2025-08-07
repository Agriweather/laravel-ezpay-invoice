<?php

namespace Agriweather\EzpayInvoice\Builders;

use Agriweather\EzpayInvoice\Options\CheckLoveCodeOptions;
use Agriweather\EzpayInvoice\Results\CheckLoveCodeResult;

class CheckLoveCodeBuilder extends Builder
{
    protected CheckLoveCodeOptions $options;

    protected function boot(): void
    {
        $this->crypto->setHashKey($this->factory->config('merchant_hash_key'));
        $this->crypto->setHashIv($this->factory->config('merchant_hash_iv'));

        $this->options = new CheckLoveCodeOptions;
        $this->options->merchantId = $this->factory->config('merchant_id');
    }

    public function getOptions(): CheckLoveCodeOptions
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
     * @param  string  $lovecode  捐贈碼，限 3~7 碼正整數
     *
     * @throws \Agriweather\EzpayInvoice\Exceptions\EzpayInvoiceException
     * @throws \Agriweather\EzpayInvoice\Exceptions\DecryptException
     */
    public function check(string $lovecode): CheckLoveCodeResult
    {
        $this->endpoint = '/Api_inv_application/checkLoveCode';

        $this->options->lovecode = $lovecode;

        $data = $this->sendRequest();

        $data['Result'] = $this->crypto->decryptPostData($data['Result']);

        return new CheckLoveCodeResult($data);
    }
}
