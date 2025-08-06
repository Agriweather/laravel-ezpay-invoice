<?php

namespace Agriweather\EzpayInvoice\Crypto;

use Agriweather\EzpayInvoice\Contracts\CheckCodeVerifiable;
use Agriweather\EzpayInvoice\Exceptions\DecryptException;
use Agriweather\EzpayInvoice\Exceptions\EncryptException;
use Agriweather\EzpayInvoice\Exceptions\InvalidCheckCodeException;
use Agriweather\EzpayInvoice\Results\Result;

class EzpayCrypto
{
    protected string $hashKey;

    protected string $hashIV;

    /**
     * 加密 post data
     *
     * @throws \Agriweather\EzpayInvoice\Exceptions\EncryptException
     */
    public function encryptPostData(array $postData): string
    {
        $postDataStr = http_build_query($postData);

        $value = openssl_encrypt(
            $this->addPadding($postDataStr),
            'AES-256-CBC',
            $this->hashKey,
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
            $this->hashIV
        );

        if ($value === false) {
            throw new EncryptException('加密錯誤');
        }

        $encryptedPostData = trim(bin2hex($value));

        return $encryptedPostData;
    }

    /**
     * 解密 post data
     *
     * @throws \Agriweather\EzpayInvoice\Exceptions\DecryptException
     */
    public function decryptPostData(string $encryptedPostData): array
    {
        $value = openssl_decrypt(
            hex2bin(trim($encryptedPostData)),
            'AES-256-CBC',
            $this->hashKey,
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
            $this->hashIV
        );

        if ($value === false) {
            throw new DecryptException('解密錯誤');
        }

        $resultStr = $this->removePadding($value);

        $result = [];

        parse_str($resultStr, $result);

        return $result;
    }

    protected function addPadding(string $string, int $blocksize = 32): string
    {
        $len = strlen($string);
        $pad = $blocksize - ($len % $blocksize);
        $string .= str_repeat(chr($pad), $pad);

        return $string;
    }

    protected function removePadding(string $string): string
    {
        $pad = ord($string[strlen($string) - 1]);

        if ($pad < 1 || $pad > 32) {
            return $string; // No padding
        }

        return substr($string, 0, -$pad);
    }

    /**
     * 生成檢查碼
     */
    public function encodeCheckValue(string $string): string
    {
        return strtoupper(hash(
            'sha256', 'HashKey='.$this->hashKey.'&'.$string.'&HashIV='.$this->hashIV
        ));
    }

    /**
     * 驗證檢查碼
     *
     * @throws \Agriweather\EzpayInvoice\Exceptions\InvalidCheckCodeException
     */
    public function verifyCheckCode(Result $result): void
    {
        if ($result instanceof CheckCodeVerifiable) {
            $checkCodeData = [
                'MerchantID' => $result->merchantID(),
                'MerchantOrderNo' => $result->orderNo(),
                'InvoiceTransNo' => $result->invoiceTransNo(),
                'TotalAmt' => $result->totalAmount(),
                'RandomNum' => $result->randomNumber(),
            ];
            ksort($checkCodeData);
            $checkStr = http_build_query($checkCodeData);
            $checkCode = strtoupper(hash(
                'sha256', 'HashIV='.$this->hashIV.'&'.$checkStr.'&HashKey='.$this->hashKey
            ));

            if ($checkCode !== $result->checkCode()) {
                throw new InvalidCheckCodeException($checkCodeData + [
                    'CheckCode' => $result->checkCode(),
                ]);
            }
        }
    }

    public function setHashKey(string $hashKey): self
    {
        $this->hashKey = $hashKey;

        return $this;
    }

    public function setHashIv(string $hashIV): self
    {
        $this->hashIV = $hashIV;

        return $this;
    }
}
