<?php

namespace Agriweather\EzpayInvoice;

use Agriweather\EzpayInvoice\Builders\CheckBarcodeBuilder;
use Agriweather\EzpayInvoice\Builders\CheckLoveCodeBuilder;
use Agriweather\EzpayInvoice\Contracts\HttpSender;
use Agriweather\EzpayInvoice\Crypto\EzpayCrypto;
use Agriweather\EzpayInvoice\Results\CheckBarcodeResult;
use Agriweather\EzpayInvoice\Results\CheckLoveCodeResult;

class CodeValidation
{
    public function __construct(
        protected Factory $factory,
        protected EzpayCrypto $crypto,
        protected HttpSender $httpSender
    ) {
        //
    }

    /**
     * 驗證手機條碼
     *
     * @param  string  $barcode  手機條碼載具，第1碼為 / + 7碼英、數字
     *
     * @throws \Agriweather\EzpayInvoice\Exceptions\EzpayInvoiceException
     * @throws \Agriweather\EzpayInvoice\Exceptions\DecryptException
     */
    public function checkBarcode(string $barcode): CheckBarcodeResult
    {
        return (new CheckBarcodeBuilder(
            $this->factory, $this->crypto, $this->httpSender
        ))->check($barcode);
    }

    /**
     * 驗證捐贈碼
     *
     * @param  string  $lovecode  捐贈碼，限 3~7 碼正整數
     *
     * @throws \Agriweather\EzpayInvoice\Exceptions\EzpayInvoiceException
     * @throws \Agriweather\EzpayInvoice\Exceptions\DecryptException
     */
    public function checkLoveCode(string $lovecode): CheckLoveCodeResult
    {
        return (new CheckLoveCodeBuilder(
            $this->factory, $this->crypto, $this->httpSender
        ))->check($lovecode);
    }
}
