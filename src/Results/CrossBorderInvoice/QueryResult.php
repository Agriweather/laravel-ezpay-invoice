<?php

namespace Agriweather\EzPayInvoice\Results\CrossBorderInvoice;

use Agriweather\EzPayInvoice\Contracts\CheckCodeVerifiable;
use Agriweather\EzPayInvoice\Enums\Invoice\InvoiceStatus;
use Agriweather\EzPayInvoice\Enums\Invoice\InvoiceType;
use Agriweather\EzPayInvoice\Enums\Invoice\InvoiceUploadStatus;
use Agriweather\EzPayInvoice\Enums\Invoice\TaxType;
use Agriweather\EzPayInvoice\Results\Concerns;
use Agriweather\EzPayInvoice\Results\Result;

final class QueryResult extends Result implements CheckCodeVerifiable
{
    use Concerns\HasCheckCode;
    use Concerns\HasCreateTime;
    use Concerns\HasInvoiceNumber;
    use Concerns\HasInvoiceTransNo;
    use Concerns\HasMerchantID;
    use Concerns\HasOrderNo;
    use Concerns\HasRandomNumber;

    /**
     * 買受人名稱
     */
    public function buyerName(): string
    {
        return $this->result['BuyerName'];
    }

    /**
     * 買受人地址
     */
    public function buyerAddress(): string
    {
        return $this->result['BuyerAddress'];
    }

    /**
     * 買受人電子信箱
     */
    public function buyerEmail(): string
    {
        return $this->result['BuyerEmail'];
    }

    /**
     * 發票字軌類型
     *
     * @throws \ValueError
     */
    public function invoiceType(): InvoiceType
    {
        return InvoiceType::from($this->result['InvoiceType']);
    }

    /**
     * 發票銷售額合計 (未稅)
     */
    public function amount(): float
    {
        return (float) $this->result['Amt'];
    }

    /**
     * 稅額
     */
    public function taxAmount(): float
    {
        return (float) $this->result['TaxAmt'];
    }

    /**
     * 發票金額
     */
    public function totalAmount(): float
    {
        return (float) $this->result['TotalAmt'];
    }

    /**
     * 商品明細
     *
     * - number: 品項序號
     * - name: 商品名稱
     * - quantity: 商品數量
     * - unit: 商品單位
     * - price: 商品單價
     * - amount: 商品金額
     * - taxType: 商品課稅別 (`null` 代表不適用)
     *
     * @return array<int, array{
     *     number: int,
     *     name: string,
     *     quantity: int,
     *     unit: string,
     *     price: float,
     *     amount: float,
     *     taxType: ?\Agriweather\EzPayInvoice\Enums\Invoice\TaxType
     * }>
     *
     * @throws \JsonException
     * @throws \ValueError
     */
    public function items(): array
    {
        if (! isset($this->result['ItemDetail']) || $this->result['ItemDetail'] === '') {
            return [];
        }

        $items = is_array($this->result['ItemDetail'])
            ? $this->result['ItemDetail']
            : json_decode($this->result['ItemDetail'], true, 512, JSON_THROW_ON_ERROR);

        return array_map(fn (array $item) => [
            'number' => $item['ItemNum'] ? (int) $item['ItemNum'] : 0,
            'name' => $item['ItemName'],
            'quantity' => $item['ItemCount'] ? (int) $item['ItemCount'] : 0,
            'unit' => $item['ItemWord'],
            'price' => $item['ItemPrice'] ? (float) $item['ItemPrice'] : 0,
            'amount' => $item['ItemAmount'] ? (float) $item['ItemAmount'] : 0,
            'taxType' => $item['ItemTaxType'] ? TaxType::from((int) $item['TaxType']) : null,
        ], $items);
    }

    /**
     * 發票狀態
     *
     * @throws \ValueError
     */
    public function invoiceStatus(): InvoiceStatus
    {
        return InvoiceStatus::from((int) $this->result['InvoiceStatus']);
    }

    /**
     * 發票上傳狀態
     *
     * 該張發票上傳財政部之狀態。
     *
     * @throws \ValueError
     */
    public function invoiceUploadStatus(): InvoiceUploadStatus
    {
        return InvoiceUploadStatus::from((int) $this->result['UploadStatus']);
    }

    /**
     * 幣別
     *
     * 該張發票的幣別代碼
     */
    public function currency(): float
    {
        return (float) $this->result['Currency'];
    }

    /**
     * 原幣金額
     *
     * 營業人備註之原幣金額
     */
    public function originalCurrencyAmount(): float
    {
        return (float) $this->result['OriginalCurrencyAmount'];
    }

    /**
     * 匯率
     *
     * 營業人備註之匯率
     */
    public function exchangeRate(): float
    {
        return (float) $this->result['ExchangeRate'];
    }
}
