<?php

namespace Agriweather\EzPayInvoice\Exceptions;

use RuntimeException;

class EzPayInvoiceException extends RuntimeException
{
    public function __construct(
        protected string $apiUrl,
        protected array $formData,
        protected string $apiStatus,
        protected string $apiMessage,
    ) {
        parent::__construct(sprintf(
            'ezPay 發票平台 API 回應錯誤 (Code: %s)：「%s」', $this->apiStatus, $this->apiMessage
        ));
    }

    /**
     * Get the exception's context information.
     *
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return [
            'url' => $this->apiUrl,
            'formdata' => $this->formData,
        ];
    }

    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }

    public function getFormData(): array
    {
        return $this->formData;
    }

    public function getApiStatus(): string
    {
        return $this->apiStatus;
    }

    public function getApiMessage(): string
    {
        return $this->apiMessage;
    }
}
