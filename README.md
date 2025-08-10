# Laravel ezPay Invoice - ezPay電子發票

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![GitHub Tests Action Status][ico-github-action]][link-github-action]
[![Total Downloads][ico-downloads]][link-downloads]

**Laravel ezPay Invoice** 是適用於 Laravel 的 ezPay 電子發票 API 套件。

實作功能：

- [x] 電子發票 API
- [ ] 電子發票 API (境外電商版)
- [x] 字軌管理 API
- [x] 手機條碼與捐證碼驗證 API

## 目錄

- [版本需求](#版本需求)
- [開始使用](#開始使用)
- [電子發票 API](#電子發票-api)
  - [開立電子發票](#開立電子發票)
  - [觸發電子發票](#觸發電子發票)
  - [查詢電子發票](#查詢電子發票)
  - [作廢電子發票](#作廢電子發票)
  - [開立折讓](#開立折讓)
  - [觸發折讓](#觸發折讓)
  - [作廢折讓](#作廢折讓)
- [電子發票 API (境外電商版)](#電子發票-api-境外電商版)
- [字軌管理 API](#字軌管理-api)
  - [準備帳號代號和金鑰](#準備帳號代號和金鑰)
  - [新增字軌](#新增字軌)
- [手機條碼與捐證碼驗證 API](#手機條碼與捐證碼驗證-api)
  - [驗證手機條碼](#驗證手機條碼)
  - [驗證捐證碼](#驗證捐證碼)
- [參考](#參考)

## 版本需求

| 版本 | PHP 版本 | Laravel 版本 |
| --- | --- | --- |
| 1.x | >=8.1 | >=9.x |

## 開始使用

使用 Composer 安裝套件：

```bash
composer require agriweather/laravel-ezpay-invoice
```

發布設置檔案：

```bash
php artisan vendor:publish --tag=ezpay-invoice-config
```

到 ezPay 電子發票的網站上註冊帳號 (測試時需在測試環境註冊測試帳號) 和建立商店。

在 ezPay 電子發票上開立發票需要有額度，如果剛開立沒有額度等情況，可以到 ezPay 電子發票的「發票管理」>「管理設定」>「使用狀況」中購買，包含測試環境也是 (測試環境依然需要點擊，但不會收費)。

開啟 ezPay 電子發票的「商店管理」中找到該商店，並複製商店串接 API 的商店代號、`HashKey` 和 `HashIV`，然後貼到 `.env` 檔案中 `EZPAY_INVOICE_MERCHANT_ID` 等參數設定：

```ini
EZPAY_INVOICE_ENV=test
EZPAY_INVOICE_MERCHANT_ID=your-merchant-id
EZPAY_INVOICE_MERCHANT_HASH_KEY=your-merchant-hash-key
EZPAY_INVOICE_MERCHANT_HASH_IV=your-merchant-hash-iv
```

`EZPAY_INVOICE_ENV` 可以設定為 `test` 或 `production`，分別對應測試環境和正式環境。

現在就可以測試開立電子發票了：

```php
use Agriweather\EzPayInvoice\Enums\TaxType;
use Agriweather\EzPayInvoice\Facades\EzPayInvoice;

$result = EzPayInvoice::invoice()
    ->create()
    ->withOrder('Order'.time())
    ->forConsumer('John Doe')
    ->withEmail('customer@example.com')
    ->withAddress('台北市信義區信義路五段7號')
    ->withItem('測試商品', quantity: 1, unit: '個', price: 1000, amount: 1000)
    ->withTax(TaxType::TAXABLE, 5)
    ->withAmount(1000, 50, 1050)
    ->issue();

$result->invoiceNumber() // 發票號碼：'AB12345678'
$result->randomNumber() // 發票隨機碼：'1234'
```

## 電子發票 API

### 開立電子發票

開立 B2C 電子發票的基本範例：

```php
$result = EzPayInvoice::invoice()
    ->create()
    ->withOrder('Order001') // 訂單編號
    ->forConsumer('John Doe') // 消費者姓名
    ->withEmail('customer@example.com') // 消費者電子信箱
    ->withAddress('台北市信義區信義路五段7號') // 消費者地址
    ->withItem('測試商品', quantity: 1, unit: '個', price: 1000, amount: 1000) // 商品名稱、數量、單位、單價和金額
    ->withTax(TaxType::TAXABLE, 5) // 稅別：應稅稅別，稅率：5%
    ->withAmount(1000, 50, 1050) // 未稅銷售額、稅額、含稅銷售額
    ->issue();
```

開立 B2C 電子發票時，可以選擇使用不同的載具：

```php
use Agriweather\EzPayInvoice\Enums\CarrierType;

EzPayInvoice::invoice()
    ->create()
    ->forConsumer('John Doe')

    // 手機條碼載具: 第1碼 / + 7碼英、數字
    ->withCarrier(CarrierType::MOBILE, '/ABC.123')
    // 自然人憑證: 2碼大寫英文 + 14碼數字
    ->withCarrier(CarrierType::CITIZEN_CERT, 'AB12345678901234')
    // ezPay 電子發票載具
    ->withCarrier(CarrierType::EZPAY_CARRIER, '1234567890')
    ->withEmail('customer@example.com') // 當選擇 ezPay 電子發票載具時，需提供電子信箱

    // 索取紙本發票
    ->withPrint()
    // 不索取紙本發票
    ->withoutPrint()
```

或者是可以提供捐贈碼，但不能與載具一起使用：

```php
EzPayInvoice::invoice()
    ->create()
    ->forConsumer('John Doe')

    // 捐贈碼: 3~7 碼純數字
    ->withLoveCode('1234567')
```

開立 B2B 電子發票的基本範例：

```php
$result = EzPayInvoice::invoice()
    ->create()
    ->withOrder('Order002')
    ->forBusiness('測試公司有限公司', '12345678') // 公司名稱、統一編號
    ->withEmail('business@company.com') // 公司電子信箱
    ->withAddress('台北市信義區信義路五段7號') // 公司地址
    ->withItem('商品A', quantity: 2, unit: '個', price: 300, amount: 600) // 商品名稱、數量、單位、單價和金額
    ->withItem('商品B', quantity: 1, unit: '個', price: 400, amount: 400) // 商品名稱、數量、單位、單價和金額
    ->withTax(TaxType::TAXABLE, 5) // 稅別：應稅稅別，稅率：5%
    ->withAmount(1000, 50, 1050) // 未稅銷售額、稅額、含稅銷售額
    ->issue();
```

設定電子發票稅別和稅率：

```php
use Agriweather\EzPayInvoice\Enums\CustomsClearance;
use Agriweather\EzPayInvoice\Enums\TaxType;

EzPayInvoice::invoice()
    ->create()

    // 應稅稅別，稅率：5%
    ->withTax(TaxType::TAXABLE, 5)

    // 零稅率
    ->withTax(TaxType::ZERO_RATE)
    // 當設定零稅率時，必須提供報關標記選項：
    ->withCustomsClearance(CustomsClearance::NON_CUSTOMS) // 非經海關
    ->withCustomsClearance(CustomsClearance::CUSTOMS) // 經海關

    // 免稅
    ->withTax(TaxType::TAX_FREE)

    // 混合應稅與免稅或零稅率 (例如：商品A應稅，商品B免稅)
    // 當開立 B2B 電子發票才能使用
    ->withTax(TaxType::MIXED)
```

設定電子發票的商品項目和銷售額：

> [!IMPORTANT]
> 銷售額計算方式，請務必與公司財會人員進行確認。

```php
EzPayInvoice::invoice()
    ->create()

    // 增加商品項目
    ->withItem('測試商品', quantity: 1, unit: '個', price: 1000, amount: 1000)
    ->withItem('Test Product', quantity: 2, unit: 'EA', price: 600, amount: 1200)

    // 批次增加商品項目
    ->withItems([
        [
            'name' => '測試商品',
            'quantity' => 1,
            'unit' => '個',
            'price' => 1000,
            'amount' => 1000,
        ],
        [
            'name' => 'Test Product',
            'quantity' => 2,
            'unit' => 'EA',
            'price' => 600,
            'amount' => 1200,
        ],
    ])

    // 未稅銷售額、稅額、含稅銷售額
    ->withAmount(2200, 110, 2310)
```

混合應稅與免稅或零稅率 的銷售額設定範例：

> [!IMPORTANT]
> 銷售額計算方式，請務必與公司財會人員進行確認。

```php
use Agriweather\EzPayInvoice\Enums\ItemTaxType;
use Agriweather\EzPayInvoice\Enums\TaxType;

EzPayInvoice::invoice()
    ->create()
    ->forBusiness('測試公司有限公司', '12345678')

    // 設定稅別為混合應稅與免稅或零稅率
    ->withTax(TaxType::MIXED)

    // 為每個商品設定不同的稅別
    ->withItem('測試商品', quantity: 1, unit: '個', price: 1000, amount: 1000, taxType: ItemTaxType::TAXABLE)
    ->withItem('測試商品', quantity: 1, unit: '個', price: 600, amount: 600, taxType: ItemTaxType::ZERO_RATE)

    // 混合稅率銷售額: 銷售額(課稅別應稅)、銷售額(課稅別零稅率)、銷售額(課稅別免稅)
    ->withMixedTaxAmount(1000, 600, 0)

    // 銷售額為上面三個 混合稅率銷售額 的總和
    ->withAmount(1600, 0, 1600)
```

設定發票備註：

```php
EzPayInvoice::invoice()
    ->create()
    ->withComment('發票備註'); // 發票備註，字數限 200 字，如有難字則再縮短
```

套件同時也提供了條件式的語法，可以在開立發票時根據需要選擇性地添加參數：

```php
use Agriweather\EzPayInvoice\Builders\Invoice\CreateBuilder;

EzPayInvoice::invoice()
    ->create()
    ->when($request->input('carrier_type') === 'mobile', function (CreateBuilder $builder) use ($mobileCarrier) {
        $builder->withCarrier(CarrierType::MOBILE, $mobileCarrier);
    })
    ->when($request->input('carrier_type') === 'lovecode', function (CreateBuilder $builder) use ($loveCode) {
        $builder->withLoveCode($loveCode);
    })
```

立即開立發票：

```php
// 立即開立發票
$result = EzPayInvoice::invoice()
    ->create()
    ...
    ->issue();

$result->invoiceNumber() // 發票號碼：'AB12345678'
$result->randomNumber() // 發票隨機碼：'1234'
$result->orderNo() // 訂單編號：'Order001'
$result->totalAmount() // 含稅銷售額：1050
```

等待觸發開立發票：

```php
// 等待觸發開立發票
// 當選擇此開立發票方式時，發票資料僅暫存於本平台，若確認要開立，則需手動呼叫 觸發電子發票 API 來開立發票。
$result = EzPayInvoice::invoice()
    ->create()
    ...
    ->deferIssue();

// 預約自動開立發票
// 當選擇此開立發票方式時，發票會於設定時間執行開立發票，若確認要提前開立，則需手動呼叫 觸發電子發票 API 來提前開立發票。
$result = EzPayInvoice::invoice()
    ->create()
    ...
    ->scheduleAt('2025-03-01'); // 設定開立發票的時間

// 保存這些資料用於觸發開立發票
$result->invoiceTransNo() // ezPay 電子發票開立序號：'25072515224376654'
$result->orderNo() // 訂單編號：'Order001'
$result->totalAmount() // 含稅銷售額：1050
```

### 觸發電子發票

```php
$result = EzPayInvoice::invoice()
    ->pending()
    ->withInvoiceTransNo('25072515224376654')
    ->withOrder('Order004')
    ->withTotalAmount(210)
    ->trigger();
```

### 查詢電子發票

//

### 作廢電子發票

//

### 開立折讓

//

### 觸發折讓

//

### 作廢折讓

//

## 電子發票 API (境外電商版)

## 字軌管理 API

## 準備帳號代號和金鑰

字軌管理和開立電子發票需要不同的帳號代號和金鑰。首先到 ezPay 電子發票的網站上的「會員管理」頁面，找到並複製會員編號、會員API串接金鑰的 `HashKey` 和 `HashIV`，然後貼到 `.env` 檔案中的 `EZPAY_INVOICE_COMPANY_ID` 等參數：

```ini
EZPAY_INVOICE_COMPANY_ID=your-company-id
EZPAY_INVOICE_COMPANY_HASH_KEY=your-company-hash-key
EZPAY_INVOICE_COMPANY_HASH_IV=your-company-hash-iv
```

### 新增字軌

//

## 手機條碼與捐證碼驗證 API

### 驗證手機條碼

//

### 驗證捐證碼

//

## 參考

[ezPay 電子發票 API 文件下載專區](https://inv.ezpay.com.tw/Invoice_index/download)

## License

基於 [MIT LICENSE](LICENSE) 釋出

[ico-version]: https://img.shields.io/packagist/v/agriweather/laravel-ezpay-invoice?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen?style=flat-square
[ico-github-action]: https://img.shields.io/github/actions/workflow/status/Agriweather/laravel-ezpay-invoice/tests.yml?branch=main&label=tests&style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/agriweather/laravel-ezpay-invoice?style=flat-square

[link-packagist]: https://packagist.org/packages/agriweather/laravel-ezpay-invoice
[link-github-action]: https://github.com/Agriweather/laravel-ezpay-invoice/actions/workflows/tests.yml?query=branch%3Amain
[link-downloads]: https://packagist.org/packages/agriweather/laravel-ezpay-invoice
