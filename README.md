# Laravel ezPay電子發票

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![GitHub Tests Action Status][ico-github-action]][link-github-action]
[![Total Downloads][ico-downloads]][link-downloads]

適用於 Laravel 的 ezPay 電子發票套件

## 實作功能

- [x] 電子發票 API
- [ ] 電子發票 API (境外電商版)
- [x] 字軌管理 API
- [x] 手機條碼與捐證碼驗證

## 版本需求

| 版本 | PHP 版本 | Laravel 版本 |
| --- | --- | --- |
| 1.x | >=8.1 | >=9.x |

## 安裝

```bash
composer require agriweather/laravel-ezpay-invoice
```

發布設置檔案：

```bash
php artisan vendor:publish --tag=ezpay-invoice-config
```

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
