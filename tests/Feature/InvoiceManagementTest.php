<?php

use Agriweather\EzpayInvoice\Factory;
use Agriweather\EzpayInvoice\Enums\InvoiceCategory;
use Agriweather\EzpayInvoice\Enums\TaxType;
use Agriweather\EzpayInvoice\Enums\CarrierType;
use Agriweather\EzpayInvoice\Exceptions\EzpayInvoiceException;
use Tests\Concerns\MocksHttpRequests;

uses(MocksHttpRequests::class);

describe('發票管理功能測試', function () {
    beforeEach(function () {
        $this->factory = app(Factory::class);
    });

    describe('發票開立流程', function () {
        it('使用者可以成功開立 B2C 發票', function () {
            $this->mockInvoiceIssueSuccess([
                'InvoiceTransNo' => '14061313541640927',
                'InvoiceNumber' => 'AA12345678',
                'RandomNum' => '1234',
                'CreateTime' => '2024-01-01 12:00:00',
            ]);

            $result = $this->factory
                ->create()
                ->withOrder('ORDER-001')
                ->forConsumer('王小明')
                ->withEmail('customer@example.com')
                ->withAddress('台北市信義區信義路五段7號')
                ->withTax(TaxType::TAXABLE, 5)
                ->withAmount(100, 5, 105)
                ->withItem('測試商品', quantity: 1, unit: '個', price: 100, amount: 100)
                ->issue();

            expect($result)->toBeArray()
                ->and($result['InvoiceNumber'])->toBe('AA12345678')
                ->and($result['RandomNum'])->toBe('1234');
        });

        it('使用者可以成功開立 B2B 發票', function () {
            $this->mockInvoiceIssueSuccess([
                'InvoiceNumber' => 'BB87654321',
                'InvoiceTransNo' => '14061313541640928',
            ]);

            $result = $this->factory
                ->create()
                ->withOrder('ORDER-002')
                ->forBusiness('測試公司有限公司', '12345678')
                ->withEmail('business@company.com')
                ->withTax(TaxType::TAXABLE, 5)
                ->withAmount(1000, 50, 1050)
                ->withItem('商品A', quantity: 2, unit: '個', price: 300, amount: 600)
                ->withItem('商品B', quantity: 1, unit: '個', price: 400, amount: 400)
                ->issue();

            expect($result)->toBeArray()
                ->and($result['InvoiceNumber'])->toBe('BB87654321');
        });

        it('使用者可以開立載具發票', function () {
            $this->mockInvoiceIssueSuccess();

            $result = $this->factory
                ->create()
                ->withOrder('ORDER-003')
                ->forConsumer('載具客戶')
                ->withCarrier('/ABC.123', CarrierType::MOBILE)
                ->withoutPrint()
                ->withTax(TaxType::TAXABLE, 5)
                ->withAmount(500, 25, 525)
                ->withItem('載具商品', quantity: 1, unit: '個', price: 500, amount: 500)
                ->issue();

            expect($result)->toBeArray();
        });

        it('使用者可以預約開立發票', function () {
            $this->mockInvoiceIssueSuccess(['Status' => 3]);

            $result = $this->factory
                ->create()
                ->withOrder('ORDER-004')
                ->forConsumer('預約客戶')
                ->withTax(TaxType::TAXABLE, 5)
                ->withAmount(200, 10, 210)
                ->withItem('預約商品', quantity: 1, unit: '個', price: 200, amount: 200)
                ->scheduleAt('2024-12-01 10:00:00');

            expect($result)->toBeArray();
        });
    });

    describe('發票查詢功能', function () {
        it('使用者可以透過訂單編號查詢發票', function () {
            $this->mockInvoiceSearchSuccess([
                'Result' => [
                    [
                        'InvoiceNumber' => 'AA12345678',
                        'MerchantOrderNo' => 'ORDER-001',
                        'TotalAmt' => '105',
                        'InvoiceStatus' => '1',
                        'CreateTime' => '2024-01-01 12:00:00',
                    ],
                ],
                'TotalCount' => 1,
            ]);

            $result = $this->factory
                ->where('order_number', 'ORDER-001')
                ->where('total_amount', 105)
                ->get();

            expect($result)->toBeArray()
                ->and($result['Result'])->toHaveCount(1)
                ->and($result['Result'][0]['InvoiceNumber'])->toBe('AA12345678');
        });

        it('使用者可以透過發票號碼查詢發票', function () {
            $this->mockInvoiceSearchSuccess([
                'Result' => [
                    [
                        'InvoiceNumber' => 'BB87654321',
                        'MerchantOrderNo' => 'ORDER-002',
                        'TotalAmt' => '1050',
                    ],
                ],
            ]);

            $result = $this->factory
                ->where('invoice_number', 'BB87654321')
                ->where('random_number', '1234')
                ->get();

            expect($result['Result'][0]['InvoiceNumber'])->toBe('BB87654321');
        });

        it('使用者可以透過日期範圍查詢發票', function () {
            $this->mockInvoiceSearchSuccess([
                'Result' => [
                    ['InvoiceNumber' => 'CC11111111'],
                    ['InvoiceNumber' => 'DD22222222'],
                ],
                'TotalCount' => 2,
            ]);

            $result = $this->factory
                ->where('date_range', ['2024-01-01', '2024-01-31'])
                ->get();

            expect($result['Result'])->toHaveCount(2)
                ->and($result['TotalCount'])->toBe(2);
        });
    });

    describe('發票作廢功能', function () {
        it('使用者可以作廢已開立的發票', function () {
            $this->mockInvoiceVoidSuccess([
                'InvoiceNumber' => 'AA12345678',
                'Status' => 1,
            ]);

            $result = $this->factory
                ->find('AA12345678')
                ->because('客戶取消訂單')
                ->void();

            expect($result)->toBeArray()
                ->and($result['InvoiceNumber'])->toBe('AA12345678');
        });
    });

    describe('發票觸發功能', function () {
        it('使用者可以觸發等待中的發票', function () {
            $this->mockInvoiceTriggerSuccess([
                'InvoiceTransNo' => '14061313541640927',
                'InvoiceNumber' => 'EE33333333',
            ]);

            $result = $this->factory
                ->where('transaction_number', '14061313541640927')
                ->withOrder('ORDER-004')
                ->withAmount(210)
                ->trigger();

            expect($result)->toBeArray()
                ->and($result['InvoiceNumber'])->toBe('EE33333333');
        });
    });

    describe('錯誤情境處理', function () {
        it('在必填欄位缺失時應拋出例外', function () {
            expect(fn() => $this->factory
                ->create()
                ->withOrder('ORDER-INVALID')
                ->issue())
                ->toThrow(EzpayInvoiceException::class);
        });

        it('在 API 回傳錯誤時應拋出例外', function () {
            $this->mockFailedApiResponse('LIB10003', '編號重複');

            expect(fn() => $this->factory
                ->create()
                ->withOrder('DUPLICATE-ORDER')
                ->forConsumer('測試客戶')
                ->withTax(TaxType::TAXABLE, 5)
                ->withAmount(100, 5, 105)
                ->withItem('商品', quantity: 1, unit: '個', price: 100, amount: 100)
                ->issue())
                ->toThrow(EzpayInvoiceException::class);
        });
    });
});