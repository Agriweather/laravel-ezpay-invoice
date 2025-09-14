<?php

namespace Agriweather\EzPayInvoice\Builders\CodeValidation;

use Agriweather\EzPayInvoice\Attributes\Resource;
use Agriweather\EzPayInvoice\Builders\Builder;
use Agriweather\EzPayInvoice\Options\CodeValidation\CodeValidationOptions;
use Agriweather\EzPayInvoice\Resources\CodeValidation;
use Agriweather\EzPayInvoice\Results\CodeValidation\CodeValidationResult;
use InvalidArgumentException;

#[Resource(CodeValidation::class)]
class CodeValidationBuilder extends Builder
{
    protected CodeValidationOptions $options;

    protected function boot(): void
    {
        $this->crypto->setHashKey($this->factory->config('merchant_hash_key'));
        $this->crypto->setHashIv($this->factory->config('merchant_hash_iv'));

        $this->options = new CodeValidationOptions;
        $this->options->merchantId = $this->factory->config('merchant_id');
    }

    protected function options(): CodeValidationOptions
    {
        return $this->options;
    }

    /**
     * 手機條碼
     *
     * @param  string  $barcode  手機條碼載具，第1碼為 / + 7碼英、數字
     */
    public function withBarcode(string $barcode): self
    {
        $this->options->barcode = $barcode;

        return $this;
    }

    /**
     * 捐贈碼
     *
     * @param  string  $lovecode  捐贈碼，限 3~7 碼正整數
     */
    public function withLovecode(string $lovecode): self
    {
        $this->options->lovecode = $lovecode;

        return $this;
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
     * 送出驗證
     *
     * @throws \RuntimeException
     * @throws \Agriweather\EzPayInvoice\Exceptions\EzPayInvoiceException
     * @throws \Agriweather\EzPayInvoice\Exceptions\DecryptException
     */
    public function check(): CodeValidationResult
    {
        if (! $this->options->barcode && ! $this->options->lovecode) {
            throw new InvalidArgumentException('需要提供手機條碼或捐贈碼');
        } elseif ($this->options->barcode && $this->options->lovecode) {
            throw new InvalidArgumentException('不能同時提供手機條碼和捐贈碼');
        }

        if ($this->options->barcode) {
            $this->endpoint = '/Api_inv_application/checkBarCode';
        } else {
            $this->endpoint = '/Api_inv_application/checkLoveCode';
        }

        $data = $this->sendRequest();

        if (! $this->factory->recording()) {
            $data['Result'] = $this->crypto->decryptByAES($data['Result']);
        }

        return new CodeValidationResult($data);
    }
}
