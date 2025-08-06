<?php

namespace Agriweather\EzpayInvoice\Builders;

use Agriweather\EzpayInvoice\Enums\CarrierType;
use Agriweather\EzpayInvoice\Enums\CustomsClearance;
use Agriweather\EzpayInvoice\Enums\InvoiceCategory;
use Agriweather\EzpayInvoice\Enums\InvoiceCreateStatus;
use Agriweather\EzpayInvoice\Enums\InvoicePrintFlag;
use Agriweather\EzpayInvoice\Enums\TaxType;
use Agriweather\EzpayInvoice\Options\InvoiceCreateOptions;
use Agriweather\EzpayInvoice\Results\InvoiceCreateResult;
use InvalidArgumentException;

class InvoiceCreateBuilder extends Builder
{
    protected InvoiceCreateOptions $options;

    protected function boot(): void
    {
        $this->crypto->setHashKey($this->factory->config('merchant_hash_key'));
        $this->crypto->setHashIv($this->factory->config('merchant_hash_iv'));

        $this->options = new InvoiceCreateOptions;
        $this->options->merchantId = $this->factory->config('merchant_id');
    }

    public function getOptions(): InvoiceCreateOptions
    {
        return $this->options;
    }

    /**
     * ezPay 平台交易序號
     *
     * 若商店同時使用 ezPay 簡單付金流服務，請於此參數傳送 ezPay 交易序號，
     * 以便對應金流交易開立發票；未使用者則不需輸入。
     */
    public function withEzPayTransNumber(string $transNumber): self
    {
        $this->options->ezPayTransNumber = $transNumber;

        return $this;
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
     * 開立發票給公司
     *
     * @param  string  $businessName  營業人名稱，長度限 60 字，若長度不足使用則帶入買方統一編號。
     * @param  string  $taxIdNumber  買受人統一編號
     */
    public function forBusiness(string $businessName, string $taxIdNumber): self
    {
        $this->options->category = InvoiceCategory::B2B;
        $this->options->buyerName = $businessName;
        $this->options->buyerTaxIdNumber = $taxIdNumber;

        return $this;
    }

    /**
     * 開立發票給消費者
     *
     * @param  string  $consumerName  個人姓名或識別碼，長度限 30 字。
     */
    public function forConsumer(string $consumerName): self
    {
        $this->options->category = InvoiceCategory::B2C;
        $this->options->buyerName = $consumerName;

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
     * @param  string  $email  買受人電子信箱，當載具類別為 ezPay 電子發票載具 (`CarrierType::EZPAY_CARRIER`) 時為必填。
     */
    public function withEmail(string $email): self
    {
        $this->options->buyerEmail = $email;

        return $this;
    }

    /**
     * 買受人載具資訊
     *
     * 當開立發票給消費者 (`forConsumer()`) 時才適用此參數。
     *
     * 當提供載具資訊時，不可再提供 **捐贈碼** (LoveCode)。
     *
     * - 手機條碼 (`CarrierType::MOBILE`): 第1碼 / + 7碼英、數字
     * - 自然人憑證 (`CarrierType::CITIZEN_CERT`): 2碼大寫英文 + 14碼數字
     * - ezPay 電子發票載具 (`CarrierType::EZPAY_CARRIER`): 提供可識別買受人之代號(例：e-mail、手機號碼、會員編號…等)，由賣方自訂即可，同一個代號則視為同一個買受人。ezPay 平台將以賣方統編加上買受人代號做為該買受人的 ezPay 電子發票載具號碼。
     */
    public function withCarrier(CarrierType $carrierType, string $carrierNumber): self
    {
        $this->options->carrierType = $carrierType;
        $this->options->carrierNumber = rawurlencode(trim($carrierNumber));

        return $this;
    }

    /**
     * 捐贈碼
     *
     * 當開立發票給消費者 (`forConsumer()`) 時才適用此參數。
     *
     * 當提供捐贈碼時，不可再提供 **買受人載具資訊** (Carrier)。
     *
     * @param  string  $loveCode  捐贈碼，3~7 碼純數字
     */
    public function withLoveCode(string $loveCode): self
    {
        $this->options->loveCode = $loveCode;

        return $this;
    }

    /**
     * 索取紙本發票
     *
     * 當開立發票給公司 (`forBusiness()`) 時，預設固定為 `true`，因此不需設定。
     *
     * 當開立發票給消費者 (`forConsumer()`)，且提供載具資訊或捐贈碼時，才需設定此參數。
     *
     * @param  bool  $print  是否索取紙本發票
     */
    public function withPrint(bool $print = true): self
    {
        $this->options->printFlag = $print
            ? InvoicePrintFlag::YES
            : InvoicePrintFlag::NO;

        return $this;
    }

    /**
     * 不索取紙本發票
     *
     * 當開立發票給公司 (`forBusiness()`) 時，預設固定為 `true`，因此不需設定。
     *
     * 當開立發票給消費者 (`forConsumer()`)，且提供載具資訊或捐贈碼時，才需設定此參數。
     */
    public function withoutPrint(): self
    {
        $this->withPrint(false);

        return $this;
    }

    /**
     * 合作超商 Kiosk 列印
     *
     * 當啟用此參數時，若此發票後續為統一發票中獎發票，
     * 則直接開放買受人 (中獎人) 可至本平台合作之超商 Kiosk
     * (目前為 全家便利商店 FamiPort) 操作列印以進行兌獎。
     *
     * @param  bool  $enabled  是否啟用
     */
    public function withKioskPrint(bool $enabled = true): self
    {
        if ($enabled) {
            $this->options->enableKioskPrint = true;
        }

        return $this;
    }

    /**
     * 稅別與稅率
     *
     * - 應稅 (`TaxType::TAXABLE`)：需要提供稅率，單位為百分比 (比如稅率 5% 時則填入 5)。
     * - 零稅率 (`TaxType::ZERO_RATE`)：不需要提供稅率，稅率自動設為 0。
     * - 免稅 (`TaxType::TAX_FREE`)：不需要提供稅率，稅率自動設為 0。
     * - 混合應稅與免稅或零稅率 (`TaxType::MIXED`)：當開立發票給公司時才可使用此參數，混合應稅與免稅或零稅率。
     *
     * @param  \Agriweather\EzpayInvoice\Enums\TaxType  $taxType  稅別
     * @param  int|float|null  $taxRate  稅率，單位為百分比 (1% = 1)
     */
    public function withTax(TaxType $taxType, int|float|null $taxRate = null): self
    {
        $this->options->taxType = $taxType;

        if ($taxType === TaxType::ZERO_RATE || $taxType === TaxType::TAX_FREE) {
            $this->options->taxRate = 0.0;
        } elseif (isset($taxRate)) {
            $this->options->taxRate = (float) $taxRate;
        }

        return $this;
    }

    /**
     * 報關標記
     *
     * 當稅別為 零稅率 (`TaxType::ZERO_RATE`) 時為必填。
     *
     * - 非經海關 (`CustomsClearance::NON_CUSTOMS`)：不需要提供海關清關資訊。
     * - 經海關 (`CustomsClearance::CUSTOMS`)：需要提供海關清關資訊。
     *
     * @param  \Agriweather\EzpayInvoice\Enums\CustomsClearance  $customsClearance  海關清關方式
     */
    public function withCustomsClearance(CustomsClearance $customsClearance): self
    {
        $this->options->customsClearance = $customsClearance;

        return $this;
    }

    /**
     * 混合稅率銷售額
     *
     * 當選擇稅別為 混合應稅與免稅或零稅率 (`TaxType::MIXED`) 時為必填。
     *
     * 若未提供應稅銷售額、零稅率銷售額或免稅銷售額，則會自動從商品項目中計算：
     *
     * - 銷售額(應稅)：將所有應稅商品的小計金額加總。
     * - 銷售額(零稅率)：將所有零稅率商品的小計金額加總。
     * - 銷售額(免稅)：將所有免稅商品的小計金額加總。
     *
     * @param  int|null  $salesAmount  應稅銷售額
     * @param  int|null  $zeroAmount  零稅率銷售額
     * @param  int|null  $freeAmount  免稅銷售額
     */
    public function withMixedTaxAmount(?int $salesAmount = null, ?int $zeroAmount = null, ?int $freeAmount = null): self
    {
        if ($this->options->taxType !== TaxType::MIXED) {
            throw new InvalidArgumentException('混合稅別銷售額僅在稅別為混合稅別時可用。');
        }

        /** @var array<int, int> */
        $amounts = [];

        foreach (($this->options->itemTaxTypes ?? []) as $i => $itemTaxType) {
            $amounts[$itemTaxType->value] = (
                $amounts[$itemTaxType->value] ?? 0
            ) + $this->options->itemAmounts[$i];
        }

        // 銷售額(應稅)
        if (isset($salesAmount)) {
            $this->options->salesAmount = $salesAmount;
        } else {
            // 若未提供應稅銷售額，則使用應稅商品金額總和
            $this->options->salesAmount = $amounts[TaxType::TAXABLE->value] ?? 0;
        }

        // 銷售額(零稅率)
        if (isset($zeroAmount)) {
            $this->options->zeroTaxAmount = $zeroAmount;
        } else {
            // 若未提供零稅率銷售額，則使用零稅率商品金額總和
            $this->options->zeroTaxAmount = $amounts[TaxType::ZERO_RATE->value] ?? 0;
        }

        // 銷售額(免稅)
        if (isset($freeAmount)) {
            $this->options->freeTaxAmount = $freeAmount;
        } else {
            // 若未提供免稅銷售額，則使用免稅商品金額總和
            $this->options->freeTaxAmount = $amounts[TaxType::TAX_FREE->value] ?? 0;
        }

        return $this;
    }

    /**
     * 銷售金額合計
     *
     * 若未提供銷售金額合計，則會自動從商品項目中計算：
     *
     * - 發票銷售額(未稅)：將所有商品小計金額加總。
     * - 發票稅額：將 發票銷售額 乘以 稅率。
     * - 發票總金額(含稅)：發票銷售額 + 發票稅額。
     *
     * @param  int|null  $amount  發票銷售額(未稅)
     * @param  int|null  $taxAmount  發票稅額
     * @param  int|null  $totalAmount  發票總金額(含稅)
     */
    public function withAmount(?int $amount = null, ?int $taxAmount = null, ?int $totalAmount = null): self
    {
        // 發票銷售額(未稅)
        if (isset($amount)) {
            $this->options->amount = $amount;
        } elseif ($this->options->taxType === TaxType::MIXED) {
            // 若未提供銷售額，且為混合稅率，則發票銷售額為 AmtSales + AmtZero + AmtFree。
            $this->options->amount = (
                ($this->options->salesAmount ?? 0) +
                ($this->options->zeroTaxAmount ?? 0) +
                ($this->options->freeTaxAmount ?? 0)
            );
        } else {
            // 若未提供銷售額，則使用商品小計金額總和
            $this->options->amount = array_sum($this->options->itemAmounts);
        }

        // 發票稅額
        if (isset($taxAmount)) {
            $this->options->taxAmount = $taxAmount;
        } else {
            // 若未提供稅額，則為銷售額 * 稅率
            $this->options->taxAmount = (int) round(
                $this->options->amount * $this->options->taxRate / 100
            );
        }

        // 發票總金額(含稅)
        if (isset($totalAmount)) {
            $this->options->totalAmount = $totalAmount;
        } else {
            // 若未提供總金額，則為銷售額 + 稅額
            $this->options->totalAmount = $this->options->amount + $this->options->taxAmount;
        }

        return $this;
    }

    /**
     * 商品項目
     *
     * - 未稅：當開立發票給公司時，商品單價和小計為未稅金額。
     * - 含稅：當開立發票給消費者時，商品單價和小計為含稅金額。
     *
     * @param  string  $name  商品名稱
     * @param  int  $quantity  商品數量
     * @param  string  $unit  商品單位
     * @param  int  $price  商品單價
     * @param  int|null  $amount  商品小計
     * @param  \Agriweather\EzpayInvoice\Enums\TaxType|null  $taxType  商品稅別
     */
    public function withItem(
        string $name,
        int $quantity,
        string $unit,
        int $price,
        ?int $amount = null,
        ?TaxType $taxType = null
    ): self {
        $this->options->itemNames[] = $name;
        $this->options->itemQuantities[] = $quantity;
        $this->options->itemUnits[] = $unit;
        $this->options->itemPrices[] = $price;
        $this->options->itemAmounts[] = is_null($amount)
            ? ($quantity * $price)
            : $amount;

        if ($this->options->taxType === TaxType::MIXED) {
            if (is_null($taxType)) {
                throw new InvalidArgumentException('當設定為混合稅別時，必須提供每個商品的稅別。');
            } elseif ($taxType === TaxType::MIXED) {
                throw new InvalidArgumentException('商品稅別不能為混合稅別。');
            }

            $this->options->itemTaxTypes[] = $taxType;
        }

        return $this;
    }

    /**
     * 批量添加商品項目
     *
     * - 未稅：當開立發票給公司時，商品單價和小計為未稅金額。
     * - 含稅：當開立發票給消費者時，商品單價和小計為含稅金額。
     *
     * @param  array  $items  商品項目陣列，每個項目必須包含 `name`、`quantity`、`unit`、`price`，參數 `amount` 和 `taxType` 為可選。
     */
    public function withItems(array $items): self
    {
        foreach ($items as $item) {
            if (! isset($item['name'], $item['quantity'], $item['unit'], $item['price'])) {
                throw new InvalidArgumentException('每個商品項目必須包含名稱、數量、單位和價格。');
            }

            $this->withItem(
                name: $item['name'],
                quantity: $item['quantity'],
                unit: $item['unit'],
                price: $item['price'],
                amount: $item['amount'] ?? null,
                taxType: $item['taxType'] ?? null
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
     * 開立發票
     *
     * @throws \Agriweather\EzpayInvoice\Exceptions\EzpayInvoiceException
     * @throws \Agriweather\EzpayInvoice\Exceptions\InvalidCheckCodeException
     */
    public function issue(): InvoiceCreateResult
    {
        $this->endpoint = '/Api/invoice_issue';

        $result = new InvoiceCreateResult($this->sendRequest());

        $this->crypto->verifyCheckCode($result);

        return $result;
    }

    /**
     * 延遲開立發票
     *
     * 於確認要開立時，再手動觸發。
     */
    public function deferIssue(): InvoiceCreateResult
    {
        $this->options->status = InvoiceCreateStatus::DEFERRED;

        return $this->issue();
    }

    /**
     * 預約自動開立發票
     *
     * @param  string  $createDate  預約開立時間，格式為 `YYYY-MM-DD`，例如 `2025-03-01`
     */
    public function scheduleAt(string $createDate): InvoiceCreateResult
    {
        $this->options->status = InvoiceCreateStatus::SCHEDULED;
        $this->options->createDate = $createDate;

        return $this->issue();
    }
}
