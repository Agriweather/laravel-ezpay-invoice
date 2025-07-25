<?php

use Agriweather\EzpayInvoice\Factory;
use Agriweather\EzpayInvoice\Enums\TaxType;
use Agriweather\EzpayInvoice\Enums\CurrencyType;
use Agriweather\EzpayInvoice\Exceptions\EzpayInvoiceException;
use Tests\Concerns\MocksHttpRequests;

uses(MocksHttpRequests::class);

describe('境外電商發票功能測試', function () {
    beforeEach(function () {
        $this->factory = app(Factory::class);
    });

    describe('境外電商發票開立', function () {
        it('使用者可以成功開立境外電商發票', function () {
            $this->mockCrossBorderInvoiceIssueSuccess([
                'InvoiceNumber' => 'CB12345678',
                'InvoiceTransNo' => '14061313541640930',
                'CreateTime' => '2024-01-01 12:00:00',
            ]);

            $result = $this->factory
                ->international()
                ->create()
                ->withOrder('CB-ORDER-001')
                ->withCustomer('海外客戶', '海外地址', 'overseas@example.com')
                ->withCurrency(CurrencyType::USD)
                ->withOriginalAmount(105.50)
                ->withExchangeRate(30.5)
                ->withAmount(3216.75, 0, 3216.75)
                ->withItem('國際商品', quantity: 1, unit: 'EA', price: 105.50, amount: 105.50)
                ->issue();

            expect($result)->toBeArray()
                ->and($result['InvoiceNumber'])->toBe('CB12345678')
                ->and($result['InvoiceTransNo'])->toBe('14061313541640930');
        });

        it('使用者可以開立多幣別境外電商發票', function () {
            $this->mockCrossBorderInvoiceIssueSuccess();

            $result = $this->factory
                ->international()
                ->create()
                ->withOrder('CB-ORDER-002')
                ->withCustomer('歐洲客戶', '歐洲地址', 'europe@example.com')
                ->withCurrency(CurrencyType::EUR)
                ->withOriginalAmount(200.00)
                ->withExchangeRate(33.8)
                ->withAmount(6760.00, 0, 6760.00)
                ->withItem('商品A', quantity: 2, unit: 'EA', price: 50.00, amount: 100.00)
                ->withItem('商品B', quantity: 1, unit: 'EA', price: 100.00, amount: 100.00)
                ->issue();

            expect($result)->toBeArray();
        });
    });

    describe('境外電商發票查詢', function () {
        it('使用者可以查詢境外電商發票', function () {
            $this->mockCrossBorderInvoiceSearchSuccess([
                'Result' => [
                    [
                        'InvoiceNumber' => 'CB12345678',
                        'MerchantOrderNo' => 'CB-ORDER-001',
                        'CurrencyCode' => 'USD',
                        'TotalAmt' => '100',
                        'CreateTime' => '2024-01-01 12:00:00',
                    ],
                ],
                'TotalCount' => 1,
            ]);

            $result = $this->factory
                ->international()
                ->where('order_number', 'CB-ORDER-001')
                ->where('total_amount', 3216.75)
                ->get();

            expect($result)->toBeArray()
                ->and($result['Result'])->toHaveCount(1)
                ->and($result['Result'][0]['CurrencyCode'])->toBe('USD');
        });

        it('使用者可以透過幣別查詢境外電商發票', function () {
            $this->mockCrossBorderInvoiceSearchSuccess([
                'Result' => [
                    ['InvoiceNumber' => 'CB11111111', 'CurrencyCode' => 'EUR'],
                    ['InvoiceNumber' => 'CB22222222', 'CurrencyCode' => 'EUR'],
                ],
                'TotalCount' => 2,
            ]);

            $result = $this->factory
                ->international()
                ->where('currency', CurrencyType::EUR)
                ->get();

            expect($result['Result'])->toHaveCount(2)
                ->and($result['Result'][0]['CurrencyCode'])->toBe('EUR');
        });
    });

    describe('境外電商折讓功能', function () {
        it('使用者可以開立境外電商折讓', function () {
            $this->mockCrossBorderAllowanceIssueSuccess([
                'AllowanceNo' => 'CBA24010001',
                'CreateTime' => '2024-01-01 12:00:00',
            ]);

            $result = $this->factory
                ->international()
                ->allowances()
                ->create()
                ->withInvoice('CB12345678')
                ->withOrder('CB-ALLOWANCE-001')
                ->withItem('退貨商品', quantity: 1, unit: 'EA', price: 50.00, amount: 50.00)
                ->withTotal(50.00)
                ->withNotification('overseas@example.com')
                ->issue();

            expect($result)->toBeArray()
                ->and($result['AllowanceNo'])->toBe('CBA24010001');
        });
    });

    describe('錯誤情境處理', function () {
        it('在匯率設定錯誤時應拋出例外', function () {
            $this->mockFailedApiResponse('LIB10030', '匯率設定錯誤');

            expect(fn() => $this->factory
                ->international()
                ->create()
                ->withOrder('INVALID-RATE')
                ->withCustomer('測試客戶', '測試地址', 'test@example.com')
                ->withCurrency(CurrencyType::USD)
                ->withOriginalAmount(100.00)
                ->withExchangeRate(-1)
                ->withAmount(100.00, 0, 100.00)
                ->withItem('商品', quantity: 1, unit: 'EA', price: 100.00, amount: 100.00)
                ->issue())
                ->toThrow(EzpayInvoiceException::class);
        });

        it('在幣別代碼錯誤時應拋出例外', function () {
            $this->mockFailedApiResponse('LIB10031', '幣別代碼錯誤');

            expect(fn() => $this->factory
                ->international()
                ->create()
                ->withOrder('INVALID-CURRENCY')
                ->withCustomer('測試客戶', '測試地址', 'test@example.com')
                ->withCurrency('XXX')
                ->withOriginalAmount(100.00)
                ->withExchangeRate(30.0)
                ->withAmount(3000.00, 0, 3000.00)
                ->withItem('商品', quantity: 1, unit: 'EA', price: 100.00, amount: 100.00)
                ->issue())
                ->toThrow(EzpayInvoiceException::class);
        });
    });
});