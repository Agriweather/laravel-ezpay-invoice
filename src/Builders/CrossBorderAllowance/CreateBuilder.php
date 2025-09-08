<?php

namespace Agriweather\EzPayInvoice\Builders\CrossBorderAllowance;

use Agriweather\EzPayInvoice\Builders\Builder;
use Agriweather\EzPayInvoice\Enums\Allowance\CreateStatus;
use Agriweather\EzPayInvoice\Options\CrossBorderAllowance\CreateOptions;
use Agriweather\EzPayInvoice\Results\CrossBorderAllowance\CreateResult;
use InvalidArgumentException;

class CreateBuilder extends Builder
{
    protected CreateOptions $options;

    protected function boot(): void
    {
        $this->crypto->setHashKey($this->factory->config('merchant_hash_key'));
        $this->crypto->setHashIv($this->factory->config('merchant_hash_iv'));

        $this->options = new CreateOptions;
        $this->options->merchantId = $this->factory->config('merchant_id');

        $this->endpoint = '/Api/crossBorderAllowanceIssue';
    }

    protected function options(): CreateOptions
    {
        return $this->options;
    }

    /**
     * 發票號碼
     *
     * @param  string  $invoiceNo  此次開立折讓的發票號碼
     */
    public function withInvoice(string $invoiceNo): self
    {
        $this->options->invoiceNo = $invoiceNo;

        return $this;
    }

    /**
     * 商店自訂訂單編號
     *
     * @param  string  $orderNo  此次開立折讓的發票，於開立發票時，提供之自訂編號。
     */
    public function withOrder(string $orderNo): self
    {
        $this->options->orderNo = $orderNo;

        return $this;
    }

    /**
     * 折讓商品項目
     *
     * @param  string  $name  折讓商品名稱
     * @param  int  $quantity  折讓商品數量
     * @param  string  $unit  折讓商品單位
     * @param  int|float  $price  折讓商品單價
     * @param  int|float  $amount  折讓商品小計
     */
    public function withItem(string $name, int $quantity, string $unit, int|float $price, int|float $amount): self
    {
        $this->options->itemNames[] = $name;
        $this->options->itemQuantities[] = $quantity;
        $this->options->itemUnits[] = $unit;
        $this->options->itemPrices[] = $price;
        $this->options->itemAmounts[] = $amount;

        // 境外電商折讓商品稅額參數為 0
        $this->options->itemTaxAmounts[] = 0;

        return $this;
    }

    /**
     * 批量添加折讓商品項目
     *
     * item 陣列需包含以下鍵值：
     *
     * - name: 折讓商品名稱
     * - quantity: 折讓商品數量
     * - unit: 折讓商品單位
     * - price: 折讓商品單價
     * - amount: 折讓商品金額
     *
     * @param  array<int, array{
     *     name: string,
     *     quantity: int,
     *     unit: string,
     *     price: int|float,
     *     amount: int|float
     * }>  $items  折讓商品項目陣列
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
     * 折讓總金額
     *
     * @param  int|float  $totalAmount  此次開立折讓加總金額。
     */
    public function withTotalAmount(int|float $totalAmount): self
    {
        $this->options->totalAmount = $totalAmount;

        return $this;
    }

    /**
     * 買受人電子信箱
     *
     * 當折讓開立時，寄送折讓相關查詢資訊至買受人的電子信箱。
     */
    public function withNotification(string $email): self
    {
        $this->options->buyerEmail = $email;

        return $this;
    }

    /**
     * 開立折讓
     *
     * @throws \Agriweather\EzPayInvoice\Exceptions\EzPayInvoiceException
     */
    public function issue(): CreateResult
    {
        return new CreateResult($this->sendRequest());
    }

    /**
     * 開立折讓並延遲確認折讓
     *
     * 待買受人確認折讓後，再向 ezPay 平台發動確認折讓。
     *
     * @throws \Agriweather\EzPayInvoice\Exceptions\EzPayInvoiceException
     */
    public function issuePendingConfirmation(): CreateResult
    {
        $this->options->status = CreateStatus::PENDING;

        return $this->issue();
    }
}
