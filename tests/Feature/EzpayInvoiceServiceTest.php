<?php

use Agriweather\EzpayInvoice\Factory;
use Agriweather\EzpayInvoice\Crypto\EzpayCrypto;
use Agriweather\EzpayInvoice\Enums\TaxType;
use Agriweather\EzpayInvoice\Enums\CurrencyType;
use Tests\Concerns\MocksHttpRequests;

uses(MocksHttpRequests::class);

describe('EzpayInvoice Factory 整合測試', function () {
    beforeEach(function () {
        $this->factory = app(Factory::class);
    });

    describe('工廠初始化與依賴注入', function () {
        it('工廠應該正確注入所需的依賴', function () {
            expect($this->factory)->toBeInstanceOf(Factory::class);
        });

        it('透過 Laravel 容器解析的工廠應該是單例', function () {
            $factory1 = app(Factory::class);
            $factory2 = app(Factory::class);

            expect($factory1)->toBe($factory2);
        });

        it('工廠實例應該包含正確的配置資訊', function () {
            expect($this->factory)->toHaveProperty('config');
        });

        it('加密服務應該正確初始化', function () {
            expect($this->factory)->toHaveProperty('crypto');
        });
    });

    describe('語義化 API 方法', function () {
        it('create() 方法應該回傳正確的 Builder', function () {
            $builder = $this->factory->create();

            expect($builder)->toBeInstanceOf(\Agriweather\EzpayInvoice\Builders\Invoice\InvoiceIssueBuilder::class);
        });

        it('where() 方法應該回傳正確的 Builder', function () {
            $builder = $this->factory->where('invoice_number', 'AA12345678');

            expect($builder)->toBeInstanceOf(\Agriweather\EzpayInvoice\Builders\Invoice\InvoiceSearchBuilder::class);
        });

        it('find() 方法應該回傳正確的 Builder', function () {
            $builder = $this->factory->find('AA12345678');

            expect($builder)->toBeInstanceOf(\Agriweather\EzpayInvoice\Builders\Invoice\InvoiceVoidBuilder::class);
        });
    });

    describe('功能群組方法', function () {
        it('allowances() 方法應該回傳正確的容器', function () {
            $container = $this->factory->allowances();

            expect($container)->toBeObject();
        });

        it('international() 方法應該回傳正確的容器', function () {
            $container = $this->factory->international();

            expect($container)->toBeObject();
        });

        it('alphanumericCode() 方法應該回傳正確的容器', function () {
            $container = $this->factory->alphanumericCode();

            expect($container)->toBeObject();
        });

        it('validation() 方法應該回傳正確的容器', function () {
            $container = $this->factory->validation();

            expect($container)->toBeObject();
        });
    });

    describe('完整工作流程測試', function () {
        it('完整的發票開立 → 查詢 → 作廢流程', function () {
            // 模擬發票開立成功
            $this->mockInvoiceIssueSuccess([
                'InvoiceNumber' => 'WF12345678',
                'InvoiceTransNo' => '14061313541640927',
                'RandomNum' => '5678',
            ]);

            // 步驟 1: 開立發票
            $issueResult = $this->factory
                ->create()
                ->withOrder('WORKFLOW-001')
                ->forConsumer('工作流程測試客戶')
                ->withEmail('workflow@example.com')
                ->withTax(TaxType::TAXABLE, 5)
                ->withAmount(500, 25, 525)
                ->withItem('工作流程商品', quantity: 1, unit: '個', price: 500, amount: 500)
                ->issue();

            expect($issueResult['InvoiceNumber'])->toBe('WF12345678');

            // 模擬發票查詢成功
            $this->mockInvoiceSearchSuccess([
                'Result' => [
                    [
                        'InvoiceNumber' => 'WF12345678',
                        'MerchantOrderNo' => 'WORKFLOW-001',
                        'InvoiceStatus' => '1',
                        'TotalAmt' => '525',
                    ],
                ],
            ]);

            // 步驟 2: 查詢發票
            $searchResult = $this->factory
                ->where('invoice_number', 'WF12345678')
                ->where('random_number', '5678')
                ->get();

            expect($searchResult['Result'][0]['InvoiceNumber'])->toBe('WF12345678');
            expect($searchResult['Result'][0]['InvoiceStatus'])->toBe('1');

            // 模擬發票作廢成功
            $this->mockInvoiceVoidSuccess([
                'InvoiceNumber' => 'WF12345678',
                'Status' => 1,
            ]);

            // 步驟 3: 作廢發票
            $voidResult = $this->factory
                ->find('WF12345678')
                ->because('工作流程測試作廢')
                ->void();

            expect($voidResult['InvoiceNumber'])->toBe('WF12345678');
        });

        it('完整的折讓開立 → 查詢 → 作廢流程', function () {
            // 模擬折讓開立成功
            $this->mockAllowanceIssueSuccess([
                'AllowanceNo' => 'AWF24010001',
                'CreateTime' => '2024-01-01 12:00:00',
            ]);

            // 步驟 1: 開立折讓
            $issueResult = $this->factory
                ->allowances()
                ->create()
                ->withInvoice('WF12345678')
                ->withOrder('ALLOWANCE-WORKFLOW-001')
                ->withItem('折讓商品', quantity: 1, unit: '個', price: 100, amount: 100, tax: 0)
                ->withTotal(105)
                ->withNotification('workflow@example.com')
                ->issue();

            expect($issueResult['AllowanceNo'])->toBe('AWF24010001');

            // 模擬折讓作廢成功
            $this->mockAllowanceVoidSuccess([
                'AllowanceNo' => 'AWF24010001',
                'Status' => 1,
            ]);

            // 步驟 2: 作廢折讓
            $voidResult = $this->factory
                ->allowances()
                ->withAllowance('AWF24010001')
                ->because('工作流程測試作廢')
                ->void();

            expect($voidResult['AllowanceNo'])->toBe('AWF24010001');
        });

        it('境外電商完整流程', function () {
            // 模擬境外電商發票開立
            $this->mockCrossBorderInvoiceIssueSuccess([
                'InvoiceNumber' => 'CBW12345678',
                'InvoiceTransNo' => '14061313541640930',
            ]);

            $issueResult = $this->factory
                ->international()
                ->create()
                ->withOrder('CB-WORKFLOW-001')
                ->withCustomer('境外客戶', '境外地址', 'overseas@example.com')
                ->withCurrency(CurrencyType::USD)
                ->withOriginalAmount(100.00)
                ->withExchangeRate(30.5)
                ->withAmount(3050.00, 0, 3050.00)
                ->withItem('境外商品', quantity: 1, unit: 'EA', price: 100.00, amount: 100.00)
                ->issue();

            expect($issueResult['InvoiceNumber'])->toBe('CBW12345678');

            // 模擬境外電商折讓開立
            $this->mockCrossBorderAllowanceIssueSuccess([
                'AllowanceNo' => 'CBAW24010001',
            ]);

            $allowanceResult = $this->factory
                ->international()
                ->allowances()
                ->create()
                ->withInvoice('CBW12345678')
                ->withOrder('CB-ALLOWANCE-WORKFLOW-001')
                ->withItem('退貨商品', quantity: 1, unit: 'EA', price: 50.00, amount: 50.00)
                ->withTotal(50.00)
                ->withNotification('overseas@example.com')
                ->issue();

            expect($allowanceResult['AllowanceNo'])->toBe('CBAW24010001');
        });
    });

    describe('錯誤處理與恢復', function () {
        it('在服務初始化失敗時應該拋出適當的例外', function () {
            config(['ezpay-invoice.merchant_id' => null]);

            expect(fn() => app()->make(Factory::class))
                ->toThrow(\Exception::class);
        });

        it('在網路連線錯誤時能正確處理', function () {
            $this->mockHttpConnectionError();

            expect(fn() => $this->factory
                ->create()
                ->withOrder('NETWORK-ERROR')
                ->forConsumer('測試客戶')
                ->withTax(TaxType::TAXABLE, 5)
                ->withAmount(100, 5, 105)
                ->withItem('商品', quantity: 1, unit: '個', price: 100, amount: 100)
                ->issue())
                ->toThrow(Exception::class);
        });
    });

    describe('效能與最佳化測試', function () {
        it('批量操作應該能正確處理', function () {
            $orders = ['BATCH-001', 'BATCH-002', 'BATCH-003'];
            $results = [];

            foreach ($orders as $order) {
                $this->mockInvoiceIssueSuccess([
                    'InvoiceNumber' => 'BATCH' . rand(10000000, 99999999),
                    'InvoiceTransNo' => '1406131354164' . rand(1000, 9999),
                ]);

                $results[] = $this->factory
                    ->create()
                    ->withOrder($order)
                    ->forConsumer('批量客戶')
                    ->withTax(TaxType::TAXABLE, 5)
                    ->withAmount(100, 5, 105)
                    ->withItem('批量商品', quantity: 1, unit: '個', price: 100, amount: 100)
                    ->issue();
            }

            expect($results)->toHaveCount(3);
            foreach ($results as $result) {
                expect($result)->toBeArray();
                expect($result)->toHaveKey('InvoiceNumber');
            }
        });
    });
});