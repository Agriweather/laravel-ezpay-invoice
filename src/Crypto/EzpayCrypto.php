<?php

namespace Agriweather\EzpayInvoice\Crypto;

use RuntimeException;

class EzpayCrypto
{
    private string $hashKey;

    private string $hashIv;

    /**
     * 建立加密服務實例
     *
     * @param  string  $hashKey  ezPay API HashKey
     * @param  string  $hashIv  ezPay API HashIV
     */
    public function __construct(string $hashKey, string $hashIv)
    {
        $this->hashKey = $hashKey;
        $this->hashIv = $hashIv;
    }

    /**
     * 加密資料為 PostData_ 格式
     *
     * @param  string  $data  要加密的資料字串
     * @return string 加密後的資料
     */
    public function encrypt(string $data): string
    {
        $paddedData = $this->addpadding($data);

        $encrypted = openssl_encrypt(
            $paddedData,
            'AES-256-CBC',
            $this->hashKey,
            OPENSSL_RAW_DATA,
            $this->hashIv
        );

        if ($encrypted === false) {
            throw new RuntimeException('加密失敗');
        }

        return trim(bin2hex($encrypted));
    }

    /**
     * 解密 PostData_ 格式的資料
     *
     * @param  string  $encryptedData  加密的資料
     * @return string 解密後的資料
     */
    public function decrypt(string $encryptedData): string
    {
        $data = hex2bin($encryptedData);

        if ($data === false) {
            throw new RuntimeException('十六進位轉換失敗');
        }

        $decrypted = openssl_decrypt(
            $data,
            'AES-256-CBC',
            $this->hashKey,
            OPENSSL_RAW_DATA,
            $this->hashIv
        );

        if ($decrypted === false) {
            throw new RuntimeException('解密失敗');
        }

        return $this->strippadding($decrypted);
    }

    /**
     * 產生 CheckCode 驗證碼
     *
     * @param  array  $data  要驗證的資料陣列
     * @return string CheckCode 驗證碼
     */
    public function generateCheckCode(array $data): string
    {
        ksort($data);
        $checkString = 'HashIV='.$this->hashIv;

        foreach ($data as $key => $value) {
            $checkString .= '&'.$key.'='.$value;
        }

        $checkString .= '&HashKey='.$this->hashKey;
        $checkString = urlencode($checkString);
        $checkString = strtolower($checkString);

        return strtoupper(hash('sha256', $checkString));
    }

    /**
     * 驗證 CheckCode 是否正確
     *
     * @param  array  $data  回傳的資料陣列
     * @param  string  $checkCode  要驗證的 CheckCode
     * @return bool 驗證結果
     */
    public function verifyCheckCode(array $data, string $checkCode): bool
    {
        $expectedCheckCode = $this->generateCheckCode($data);

        return hash_equals($expectedCheckCode, $checkCode);
    }

    /**
     * 為資料添加填充
     *
     * @param  string  $string  原始字串
     * @return string 填充後的字串
     */
    private function addpadding(string $string): string
    {
        $blocksize = 32;
        $len = strlen($string);
        $pad = $blocksize - ($len % $blocksize);
        $string .= str_repeat(chr($pad), $pad);

        return $string;
    }

    /**
     * 移除資料填充
     *
     * @param  string  $string  填充後的字串
     * @return string 移除填充後的字串
     */
    private function strippadding(string $string): string
    {
        $slast = ord(substr($string, -1));
        $slastc = chr($slast);
        $pcheck = substr($string, -$slast);

        if (preg_match("/$slastc{".$slast.'}/', $string)) {
            $string = substr($string, 0, strlen($string) - $slast);

            return $string;
        } else {
            return false;
        }
    }
}
