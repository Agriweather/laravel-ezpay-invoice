<?php

namespace Agriweather\EzpayInvoice\Builders;

use Agriweather\EzpayInvoice\Enums\CurrencyType;
use Agriweather\EzpayInvoice\Enums\InvoiceCreateStatus;
use Agriweather\EzpayInvoice\Options\CrossBorderInvoiceCreateOptions;
use Agriweather\EzpayInvoice\Results\CrossBorderInvoiceCreateResult;
use InvalidArgumentException;

class CrossBorderInvoiceCreateBuilder extends Builder
{
    protected CrossBorderInvoiceCreateOptions $options;

    protected function boot(): void
    {
        $this->crypto->setHashKey($this->factory->config('merchant_hash_key'));
        $this->crypto->setHashIv($this->factory->config('merchant_hash_iv'));

        $this->options = new CrossBorderInvoiceCreateOptions;
        $this->options->merchantId = $this->factory->config('merchant_id');
    }

    public function getOptions(): CrossBorderInvoiceCreateOptions
    {
        return $this->options;
    }

    /**
     * 商店自訂訂單編號
     *
     * @param  string  $orderNo  商店自訂訂單編號，限英、數字、_ 格式。同一商店中此編號不可重覆。
     */
    public function withOrder(string $orderNo): self
    {
        $this->options->orderNo = $orderNo;

        return $this;
    }

    /**
     * 買受人姓名
     *
     * @param  string  $customerName  個人姓名或營業人名稱，長度限 30 字。
     */
    public function withCustomer(string $customerName): self
    {
        $this->options->buyerName = $customerName;

        return $this;
    }

    /**
     * 買受人地址
     *
     * @param  string  $address  買受人的聯絡地址
     */
    public function withAddress(string $address): self
    {
        $this->options->buyerAddress = $address;

        return $this;
    }

    /**
     * 買受人電子信箱
     *
     * @param  string  $email  買受人電子信箱
     */
    public function withEmail(string $email): self
    {
        $this->options->buyerEmail = $email;

        return $this;
    }

    /**
     * 銷售金額合計
     *
     * **銷售額計算方式，請務必與公司財會人員進行確認。**
     *
     * @param  int|float  $amount  發票銷售額(未稅)。
     * @param  int|float  $taxAmount  發票稅額。
     * @param  int|float  $totalAmount  發票總金額(含稅)，發票銷售額 + 發票稅額。
     */
    public function withAmount(int|float $amount, int|float $taxAmount, int|float $totalAmount): self
    {
        $this->options->amount = $amount;
        $this->options->taxAmount = $taxAmount;
        $this->options->totalAmount = $totalAmount;

        return $this;
    }

    /**
     * 商品項目
     *
     * @param  string  $name  商品名稱
     * @param  int|float  $quantity  商品數量
     * @param  string  $unit  商品單位
     * @param  int|float  $price  商品單價
     * @param  int|float  $amount  商品小計
     */
    public function withItem(string $name, int|float $quantity, string $unit, int|float $price, int|float $amount): self
    {
        $this->options->itemNames[] = $name;
        $this->options->itemQuantities[] = $quantity;
        $this->options->itemUnits[] = $unit;
        $this->options->itemPrices[] = $price;
        $this->options->itemAmounts[] = $amount;

        return $this;
    }

    /**
     * 批量添加商品項目
     *
     * item 陣列需包含以下鍵值：
     *
     * - name: 商品名稱
     * - quantity: 商品數量
     * - unit: 商品單位
     * - price: 商品單價
     * - amount: 商品金額
     *
     * @param  array<int, array{
     *     name: string,
     *     quantity: int,
     *     unit: string,
     *     price: int,
     *     amount: int
     * }>  $items  商品項目陣列
     */
    public function withItems(array $items): self
    {
        foreach ($items as $item) {
            if (! isset($item['name'], $item['quantity'], $item['unit'], $item['price'], $item['amount'])) {
                throw new InvalidArgumentException('每個商品項目必須包含名稱、數量、單位、價格和小計。');
            }

            $this->withItem(
                name: $item['name'],
                quantity: $item['quantity'],
                unit: $item['unit'],
                price: $item['price'],
                amount: $item['amount']
            );
        }

        return $this;
    }

    /**
     * 發票備註
     *
     * @param  string  $comment  發票備註，字數限 200 字，如有難字則再縮短。
     */
    public function withComment(string $comment): self
    {
        $this->options->comment = $comment;

        return $this;
    }

    /**
     * 幣別
     *
     * @param  \Agriweather\EzpayInvoice\Enums\CurrencyType  $currency  幣別代碼
     */
    public function withCurrency(CurrencyType $currency): self
    {
        $this->options->currency = $currency;

        return $this;
    }

    /**
     * 原幣金額
     */
    public function withOriginalCurrencyAmount(int|float $amount): self
    {
        $this->options->originalCurrencyAmount = $amount;

        return $this;
    }

    /**
     * 匯率
     */
    public function withExchangeRate(float $exchangeRate): self
    {
        $this->options->exchangeRate = $exchangeRate;

        return $this;
    }

    /**
     * 開立發票
     *
     * @throws \Agriweather\EzpayInvoice\Exceptions\EzpayInvoiceException
     * @throws \Agriweather\EzpayInvoice\Exceptions\InvalidCheckCodeException
     */
    public function issue(): CrossBorderInvoiceCreateResult
    {
        $this->endpoint = '/Api/crossBorderInvoiceIssue';

        $result = new CrossBorderInvoiceCreateResult($this->sendRequest());

        $this->crypto->verifyCheckCode($result);

        return $result;
    }

    /**
     * 延遲開立發票
     *
     * 於確認要開立時，再手動觸發。
     *
     * @throws \Agriweather\EzpayInvoice\Exceptions\EzpayInvoiceException
     * @throws \Agriweather\EzpayInvoice\Exceptions\InvalidCheckCodeException
     */
    public function deferIssue(): CrossBorderInvoiceCreateResult
    {
        $this->options->status = InvoiceCreateStatus::DEFERRED;

        return $this->issue();
    }

    /**
     * 預約自動開立發票
     *
     * @param  string  $createDate  預約開立時間，格式為 `YYYY-MM-DD`，例如 `2025-03-01`
     *
     * @throws \Agriweather\EzpayInvoice\Exceptions\EzpayInvoiceException
     * @throws \Agriweather\EzpayInvoice\Exceptions\InvalidCheckCodeException
     */
    public function scheduleAt(string $createDate): CrossBorderInvoiceCreateResult
    {
        $this->options->status = InvoiceCreateStatus::SCHEDULED;
        $this->options->createDate = $createDate;

        return $this->issue();
    }
}
