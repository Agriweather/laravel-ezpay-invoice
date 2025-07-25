<?php

use Agriweather\EzpayInvoice\Factory;
use Agriweather\EzpayInvoice\Enums\TaxType;
use Agriweather\EzpayInvoice\Exceptions\EzpayInvoiceException;
use Tests\Concerns\MocksHttpRequests;

uses(MocksHttpRequests::class);

describe('折讓管理功能測試', function () {
    beforeEach(function () {
        $this->factory = app(Factory::class);
    });

    describe('折讓開立流程', function () {
        it('使用者可以成功開立一般折讓', function () {
            $this->mockAllowanceIssueSuccess([
                'AllowanceNo' => 'AL24010001',
                'CreateTime' => '2024-01-01 12:00:00',
                'AllowanceUrl' => 'https://test.ezpay.com.tw/allowance',
            ]);

            $result = $this->factory
                ->allowances()
                ->create()
                ->withInvoice('AA12345678')
                ->withOrder('ALLOWANCE-001')
                ->withItem('退貨商品', quantity: 1, unit: '個', price: 50, amount: 50, tax: 0)
                ->withTotal(52)
                ->withNotification('customer@example.com')
                ->issue();

            expect($result)->toBeArray()
                ->and($result['AllowanceNo'])->toBe('AL24010001')
                ->and($result['AllowanceUrl'])->toContain('ezpay.com.tw');
        });

        it('使用者可以開立多品項折讓', function () {
            $this->mockAllowanceIssueSuccess();

            $result = $this->factory
                ->allowances()
                ->create()
                ->withInvoice('BB87654321')
                ->withOrder('ALLOWANCE-002')
                ->withItem('商品A', quantity: 1, unit: '個', price: 100, amount: 100, tax: 0)
                ->withItem('商品B', quantity: 1, unit: '個', price: 50, amount: 50, tax: 0)
                ->withTotal(157)
                ->withNotification('company@example.com')
                ->issue();

            expect($result)->toBeArray();
        });

        it('使用者可以預約開立折讓', function () {
            $this->mockAllowanceIssueSuccess(['Status' => 3]);

            $result = $this->factory
                ->allowances()
                ->create()
                ->withInvoice('CC11111111')
                ->withOrder('ALLOWANCE-003')
                ->withItem('預約商品', quantity: 1, unit: '個', price: 30, amount: 30, tax: 0)
                ->withTotal(31)
                ->issue();

            expect($result)->toBeArray();
        });
    });

    describe('折讓作廢功能', function () {
        it('使用者可以作廢已開立的折讓', function () {
            $this->mockAllowanceVoidSuccess([
                'AllowanceNo' => 'AL24010001',
                'Status' => 1,
            ]);

            $result = $this->factory
                ->allowances()
                ->withAllowance('AL24010001')
                ->because('折讓錯誤')
                ->void();

            expect($result)->toBeArray()
                ->and($result['AllowanceNo'])->toBe('AL24010001');
        });
    });

    describe('折讓觸發功能', function () {
        it('使用者可以觸發等待中的折讓', function () {
            $this->mockAllowanceTriggerSuccess([
                'AllowanceTransNo' => '14061313541640929',
                'AllowanceNo' => 'AL24010002',
            ]);

            $result = $this->factory
                ->allowances()
                ->withAllowance('AL24010002')
                ->withOrder('ALLOWANCE-002')
                ->withAmount(157)
                ->confirm();

            expect($result)->toBeArray()
                ->and($result['AllowanceNo'])->toBe('AL24010002');
        });
    });

    describe('錯誤情境處理', function () {
        it('在原發票不存在時應拋出例外', function () {
            $this->mockFailedApiResponse('LIB10025', '原發票不存在');

            expect(fn() => $this->factory
                ->allowances()
                ->create()
                ->withInvoice('INVALID123456')
                ->withOrder('INVALID-ALLOWANCE')
                ->withItem('商品', quantity: 1, unit: '個', price: 100, amount: 100, tax: 0)
                ->withTotal(105)
                ->issue())
                ->toThrow(EzpayInvoiceException::class, '原發票不存在');
        });

        it('在發票隨機碼錯誤時應拋出例外', function () {
            $this->mockFailedApiResponse('LIB10026', '發票隨機碼錯誤');

            expect(fn() => $this->factory
                ->allowances()
                ->create()
                ->withInvoice('AA12345678')
                ->withOrder('WRONG-RANDOM')
                ->withItem('商品', quantity: 1, unit: '個', price: 100, amount: 100, tax: 0)
                ->withTotal(105)
                ->issue())
                ->toThrow(EzpayInvoiceException::class, '發票隨機碼錯誤');
        });

        it('在折讓金額超過原發票時應拋出例外', function () {
            $this->mockFailedApiResponse('LIB10027', '折讓金額超過原發票');

            expect(fn() => $this->factory
                ->allowances()
                ->create()
                ->withInvoice('AA12345678')
                ->withOrder('EXCEED-AMOUNT')
                ->withItem('商品', quantity: 1, unit: '個', price: 999999, amount: 999999, tax: 0)
                ->withTotal(999999)
                ->issue())
                ->toThrow(EzpayInvoiceException::class, '折讓金額超過原發票');
        });
    });
});