<?php

namespace Agriweather\EzPayInvoice\Results\CrossBorderInvoice;

use Agriweather\EzPayInvoice\Contracts\CheckCodeVerifiable;
use Agriweather\EzPayInvoice\Enums\Invoice\CarrierType;
use Agriweather\EzPayInvoice\Enums\Invoice\InvoicePrintFlag;
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
     * 課稅別
     *
     * @throws \ValueError
     */
    // public function taxType(): TaxType
    // {
    //     return TaxType::from((int) $this->result['TaxType']);
    // }

    /**
     * 稅率
     */
    // public function taxRate(): float
    // {
    //     return (float) $this->result['TaxRate'];
    // }

    /**
     * 發票銷售額合計 (未稅)
     */
    public function amount(): float
    {
        return (float) $this->result['Amt'];
    }

    /**
     * 銷售額 (課稅別應稅的未稅金額)
     */
    // public function salesAmount(): ?int
    // {
    //     return isset($this->result['AmtSales'])
    //         ? (int) $this->result['AmtSales']
    //         : null;
    // }

    /**
     * 銷售額 (課稅別零稅率的未稅金額)
     */
    // public function zeroAmount(): ?int
    // {
    //     return isset($this->result['AmtZero'])
    //         ? (int) $this->result['AmtZero']
    //         : null;
    // }

    /**
     * 銷售額 (課稅別免稅的未稅金額)
     */
    // public function freeAmount(): ?int
    // {
    //     return isset($this->result['AmtFree'])
    //         ? (int) $this->result['AmtFree']
    //         : null;
    // }

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
     * 載具類別
     *
     * @throws \ValueError
     */
    // public function carrierType(): ?CarrierType
    // {
    //     return isset($this->result['CarrierType']) && $this->result['CarrierType'] !== ''
    //         ? CarrierType::from((int) $this->result['CarrierType'])
    //         : null;
    // }

    /**
     * 載具編號
     */
    // public function carrierNumber(): ?string
    // {
    //     return $this->result['CarrierNum'] ?: null;
    // }

    /**
     * 捐贈碼
     */
    // public function loveCode(): ?string
    // {
    //     return $this->result['LoveCode'] ?: null;
    // }

    /**
     * 是否索取紙本發票
     *
     * @throws \ValueError
     */
    // public function printFlag(): bool
    // {
    //     return $this->result['PrintFlag'] === InvoicePrintFlag::YES->value;
    // }

    /**
     * 是否開放至合作超商 Kiosk 列印
     *
     * 該張發票是否開放買受人可至本平台合作之超商 Kiosk 進行列印。
     */
    // public function kioskPrintFlag(): bool
    // {
    //     return $this->result['KioskPrintFlag'] === '1';
    // }

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
     *     price: int,
     *     amount: int,
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
