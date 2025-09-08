# Laravel ezPay Invoice - ezPay電子發票

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![GitHub Tests Action Status][ico-github-action]][link-github-action]
[![Total Downloads][ico-downloads]][link-downloads]

**Laravel ezPay Invoice** 是適用於 Laravel 的 ezPay 電子發票 API 套件。由 [Lucas Yang](https://github.com/ycs77) 建立，並由 [阿龜微氣候天眼通](https://github.com/Agriweather) 團隊維護。

套件功能：

- 電子發票 API
- 電子發票 API (境外電商版)
- 字軌管理 API
- 手機條碼與捐證碼驗證 API

## 目錄

- [版本需求](#版本需求)
- [開始使用](#開始使用)
- [電子發票 API](#電子發票-api)
  - [開立電子發票](#開立電子發票)
  - [觸發電子發票](#觸發電子發票)
  - [查詢電子發票](#查詢電子發票)
  - [作廢電子發票](#作廢電子發票)
  - [開立折讓](#開立折讓)
  - [延遲確認折讓](#延遲確認折讓)
  - [作廢折讓](#作廢折讓)
- [電子發票 API (境外電商版)](#電子發票-api-境外電商版)
- [字軌管理 API](#字軌管理-api)
  - [準備帳號代號和金鑰](#準備帳號代號和金鑰)
  - [申請新字軌](#申請新字軌)
  - [查詢字軌](#查詢字軌)
  - [啟用字軌](#啟用字軌)
  - [暫停字軌](#暫停字軌)
  - [停用字軌](#停用字軌)
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

在 ezPay 電子發票上開立發票需要有額度，如果剛開立沒有額度等情況，可以到 ezPay 電子發票的「發票管理」>「管理設定」>「使用狀況」中購買，包含測試環境也是。 (測試環境依然需要點擊，但不會收費)

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
use Agriweather\EzPayInvoice\Enums\Invoice\TaxType;
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
use Agriweather\EzPayInvoice\Enums\Invoice\CarrierType;

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
use Agriweather\EzPayInvoice\Enums\Invoice\CustomsClearance;
use Agriweather\EzPayInvoice\Enums\Invoice\TaxType;

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
use Agriweather\EzPayInvoice\Enums\Invoice\ItemTaxType;
use Agriweather\EzPayInvoice\Enums\Invoice\TaxType;

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

### 觸發開立電子發票

如果使用了 **等待觸發開立發票** (`deferIssue()`) 的方式，需要呼叫觸發電子發票 API 來完成開立發票。如果是 **預約自動開立發票** (`scheduleAt()`) 則是可以透過呼叫觸發 API 來提前開立發票：

```php
$result = EzPayInvoice::invoice()
    ->pending()
    ->withInvoiceTransNo('25072515224376654')
    ->withOrder('Order004')
    ->withTotalAmount(210)
    ->trigger();
```

### 查詢電子發票

透過發票號碼和隨機碼查詢電子發票：

```php
$invoiceResult = EzPayInvoice::invoice()
    ->query()
    ->withInvoice('GG72002017')
    ->withRandomNumber('1234')
    ->get();
```

或者也可以透過訂單編號及發票金額查詢電子發票：

```php
$invoiceResult = EzPayInvoice::invoice()
    ->query()
    ->withOrder('Order001')
    ->withTotalAmount(1050)
    ->get();
```

電子發票查詢結果包含以下資訊：

```php
$invoiceResult->invoiceNumber() // 發票號碼：'GG72002017'
$invoiceResult->randomNumber() // 發票隨機碼：'1234'
$invoiceResult->orderNo() // 訂單編號：'Order001'
$invoiceResult->invoiceTransNo() // ezPay 電子發票開立序號：'25072515224376654'

$invoiceResult->invoiceStatus() // 發票狀態：`InvoiceStatus::ISSUED` (已開立)
$invoiceResult->invoiceUploadStatus() // 發票上傳財政部之狀態：`InvoiceUploadStatus::UPLOADED` (已上傳)

$invoiceResult->buyerName() // 買受人名稱：'John Doe'
$invoiceResult->buyerTaxIdNumber() // 買受人統一編號：'12345678'
$invoiceResult->buyerAddress() // 買受人地址：'台北市信義區信義路五段7號'
$invoiceResult->buyerPhone() // 買受人電話：'02-12345678'
$invoiceResult->buyerEmail() // 買受人電子信箱：'customer@example.com'

$invoiceResult->invoiceType() // 發票字軌類型：`InvoiceType::GENERAL` (07: 一般稅額計算)
$invoiceResult->category() // 發票種類：`InvoiceCategory::B2C` (B2C電子發票)
$invoiceResult->taxType() // 課稅別：`TaxType::TAXABLE` (應稅)
$invoiceResult->taxRate() // 稅率：5.0 (5%)

$invoiceResult->amount() // 發票銷售額合計 (未稅)：1000
$invoiceResult->salesAmount() // 銷售額 (課稅別應稅的未稅金額)：300
$invoiceResult->zeroAmount() // 銷售額 (課稅別零稅率的未稅金額)：350
$invoiceResult->freeAmount() // 銷售額 (課稅別免稅的未稅金額)：300
$invoiceResult->taxAmount() // 稅額：50
$invoiceResult->totalAmount() // 含稅銷售額：1050

$invoiceResult->carrierType() // 載具類型：`CarrierType::MOBILE` (手機條碼載具)
$invoiceResult->carrierNumber() // 載具編號：'/ABC.123'
$invoiceResult->loveCode() // 捐贈碼：'1234567'
$invoiceResult->printFlag() // 是否索取紙本發票：true (索取紙本發票)
$invoiceResult->kioskPrintFlag() // 是否開放至合作超商 Kiosk 列印：true (開放列印)

$items = $invoiceResult->items() // 商品項目陣列
// [
//     [
//         'number' => 1,
//         'name' => '測試商品',
//         'quantity' => 1,
//         'unit' => '個',
//         'price' => 1000,
//         'amount' => 1000,
//         'taxType' => TaxType::TAXABLE, // 應稅
//     ],
//     [
//         'number' => 2,
//         'name' => 'Test Product',
//         'quantity' => 2,
//         'unit' => 'EA',
//         'price' => 600,
//         'amount' => 1200,
//         'taxType' => TaxType::ZERO_RATE, // 零稅率
//     ],
// ]
```

### 作廢電子發票

作廢電子發票需要傳入發票號碼和作廢原因：

```php
EzPayInvoice::invoice()
    ->voidable()
    ->withInvoice('GG72002017')
    ->because('客戶取消訂單')
    ->invalidate();
```

### 開立折讓

當需要對已開立的電子發票進行部分或全部退貨時，可以立即開立發票折讓 (同時會立即確認折讓)：

```php
$result = EzPayInvoice::allowance()
    ->create()
    ->withInvoice('GG72002018')
    ->withOrder('Order001')
    ->withItem('退貨商品', quantity: 2, unit: '個', price: 300, amount: 600, taxAmount: 30)
    ->withTotalAmount(630)
    ->withNotification('customer@example.com')
    ->issue();

$result->allowanceNo() // 折讓號：'A250725235346456'
$result->orderNo() // 訂單編號：'Order001'
$result->invoiceNumber() // 發票號碼：'GG72002018'
$result->allowanceAmount() // 折讓金額：630
$result->remainingAmount() // 折讓後剩餘發票金額：420
```

### 延遲確認折讓

開立延遲確認的折讓，待買受人確認折讓後，再向 ezPay 平台發動確認折讓：

```php
$result = EzPayInvoice::allowance()
    ->create()
    ...
    ->issuePendingConfirmation();
```

買受人發動確認折讓：

```php
EzPayInvoice::allowance()
    ->pending()
    ->withAllowance('A250726001830959')
    ->withOrder('Order001')
    ->withTotalAmount(420)
    ->confirm();
```

買受人發動取消折讓：

```php
EzPayInvoice::allowance()
    ->pending()
    ->withAllowance('A250726001830959')
    ->withOrder('Order001')
    ->withTotalAmount(420)
    ->cancel();
```

### 作廢折讓

作廢已開立的折讓，需傳入折讓號和作廢原因：

```php
EzPayInvoice::allowance()
    ->voidable()
    ->withAllowance('A250726001830959')
    ->because('作廢原因')
    ->invalidate();
```

## 電子發票 API (境外電商版)

//

## 字軌管理 API

### 準備帳號代號和金鑰

字軌管理和開立電子發票需要不同的帳號代號和金鑰。首先到 ezPay 電子發票的網站上的「會員管理」頁面，找到並複製會員編號、會員API串接金鑰的 `HashKey` 和 `HashIV`，然後貼到 `.env` 檔案中的 `EZPAY_INVOICE_COMPANY_ID` 等參數：

```ini
EZPAY_INVOICE_COMPANY_ID=your-company-id
EZPAY_INVOICE_COMPANY_HASH_KEY=your-company-hash-key
EZPAY_INVOICE_COMPANY_HASH_IV=your-company-hash-iv
```

### 申請新字軌

使用新增字軌 API 來申請新字軌：

```php
use Agriweather\EzPayInvoice\Enums\Invoice\InvoiceTerm;
use Agriweather\EzPayInvoice\Enums\Invoice\InvoiceType;

$result = EzPayInvoice::alphanumericCode()
    ->create()
    ->withYear(113) // 民國年，只可輸入今年與明年。
    ->withTerm(InvoiceTerm::JUL_AUG) // 發票期別：07-08月
    ->withCode('AA') // 字軌英文代碼
    ->withRange('24000100', '24000199') // 發票號碼範圍
    ->withType(InvoiceType::GENERAL) // 發票類別：`InvoiceType::GENERAL` (07: 一般稅額計算)
    ->save();

$result->managementNo() // 字軌管理編號：'0t0ghr0fyv'
$result->lastNumber() // 該組字軌剩餘張數：100
$result->status() // 字軌狀態： `AlphanumericCodeStatus::ACTIVE` (啟用)
```

### 查詢字軌

使用發票年度和期別來查詢字軌資訊：

```php
$alphanumericCodeResults = EzPayInvoice::alphanumericCode()
    ->query()
    ->withYear(113)
    ->withTerm(InvoiceTerm::JUL_AUG)
    ->get();

foreach ($alphanumericCodeResults as $result) {
    $result->managementNo() // 字軌管理編號：'0t0ghr0fyv'
    $result->year() // 發票年度：113
    $result->term() // 發票期別：`InvoiceTerm::JUL_AUG` (07-08月)
    $result->alphanumericCode() // 字軌英文代碼：'AA'
    $result->startNumber() // 字軌起號：'24000100'
    $result->endNumber() // 字軌迄號：'24000199'
    $result->lastNumber() // 該組字軌剩餘張數：100
    $result->type() // 發票類別：`InvoiceType::GENERAL` (07: 一般稅額計算)
    $result->status() // 字軌狀態： `AlphanumericCodeStatus::ACTIVE` (啟用)
}
```

### 啟用字軌

如果字軌是暫停狀態，可以啟用字軌。需要傳入字軌管理編號和發票年度：

```php
EzPayInvoice::alphanumericCode()
    ->query()
    ->withNo('0t0ghr0fyv')
    ->withYear(113)
    ->enable();
```

### 暫停字軌

如果字軌是啟用狀態，可以暫停字軌。需要傳入字軌管理編號和發票年度：

```php
$result = EzPayInvoice::alphanumericCode()
    ->query()
    ->withNo('0t0ghr0fyv')
    ->withYear(113)
    ->pause();
```

### 停用字軌

可以停用字軌，但需要注意的是，停用後就無法再啟用該字軌。需要傳入字軌管理編號和發票年度：

```php
EzPayInvoice::alphanumericCode()
    ->query()
    ->withNo('0t0ghr0fyv')
    ->withYear(113)
    ->disable();
```

## 手機條碼與捐證碼驗證 API

### 驗證手機條碼

驗證手機條碼是否存在於財政部電子發票整合服務平台：

```php
$result = EzPayInvoice::codeValidation()
    ->withBarcode('/ABC.123')
    ->check();

$result->isValid() // 手機條碼是否有效：true
```

### 驗證捐證碼

驗證捐證碼是否存在於財政部電子發票整合服務平台：

```php
$result = EzPayInvoice::codeValidation()
    ->withLoveCode('123')
    ->check();

$result->isValid() // 捐證碼是否有效：true
```

## API 參考文件

[ezPay 電子發票 API 文件下載專區](https://inv.ezpay.com.tw/Invoice_index/download)

當前參考文件版本：

- 電子發票API v1.2.2 (2024/05/09)
- 電子發票API_境外電商版 v1.0.0 (2021/02/02)
- 字軌管理API v1.0.0 (2018/10/08)
- 手機條碼與捐證碼驗證技術串接手冊 v1.0.0 (2021/03/03)

## 貢獻專案

歡迎參與貢獻專案，請參考 [貢獻指南](CONTRIBUTING.md) 文件。

## License

基於 [MIT LICENSE](LICENSE) 釋出

[ico-version]: https://img.shields.io/packagist/v/agriweather/laravel-ezpay-invoice?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen?style=flat-square
[ico-github-action]: https://img.shields.io/github/actions/workflow/status/Agriweather/laravel-ezpay-invoice/tests.yml?branch=main&label=tests&style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/agriweather/laravel-ezpay-invoice?style=flat-square

[link-packagist]: https://packagist.org/packages/agriweather/laravel-ezpay-invoice
[link-github-action]: https://github.com/Agriweather/laravel-ezpay-invoice/actions/workflows/tests.yml?query=branch%3Amain
[link-downloads]: https://packagist.org/packages/agriweather/laravel-ezpay-invoice
