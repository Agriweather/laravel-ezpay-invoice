<?php

namespace Agriweather\EzpayInvoice\Exceptions;

use Exception;

/**
 * ezPay 電子發票 API 例外
 */
class EzpayInvoiceException extends Exception
{
    protected string $errorCode;

    /**
     * 建立例外實例
     *
     * @param  string  $message  錯誤訊息
     * @param  string  $errorCode  ezPay 錯誤代碼
     * @param  int  $code  HTTP 狀態碼
     * @param  Exception|null  $previous  前一個例外
     */
    public function __construct(
        string $message,
        string $errorCode = '',
        int $code = 0,
        ?Exception $previous = null
    ) {
        $this->errorCode = $errorCode;
        parent::__construct($message, $code, $previous);
    }

    /**
     * 取得 ezPay 錯誤代碼
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}
