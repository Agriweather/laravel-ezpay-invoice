<?php

use Agriweather\EzpayInvoice\Factory;

// use Agriweather\EzpayInvoice\Enums\InvoiceTerm;
// use Agriweather\EzpayInvoice\Enums\InvoiceType;

describe('字軌管理功能測試', function () {
    beforeEach(function () {
        $this->factory = app(Factory::class);
    });

    describe('字軌申請功能', function () {
        it('可以成功申請新字軌', function () {
            // $this->mockAlphanumericCodeCreateSuccess([
            //     'TrackCode' => '00455ujp8',
            //     'Year' => 113,
            //     'Prefix' => 'AA',
            //     'StartNumber' => '00000001',
            //     'EndNumber' => '99999999',
            //     'Status' => 1,
            // ]);

            // $result = $this->factory
            //     ->alphanumericCode()
            //     ->create()
            //     ->withYear(113)
            //     ->withTerm(InvoiceTerm::FIRST)
            //     ->withCode('AA')
            //     ->withRange('00000001', '99999999')
            //     ->withType(InvoiceType::GENERAL)
            //     ->save();

            // expect($result)->toBeArray()
            //     ->and($result['TrackCode'])->toBe('00455ujp8')
            //     ->and($result['Year'])->toBe(113)
            //     ->and($result['Prefix'])->toBe('AA');
        })->todo();
    });

    describe('字軌查詢功能', function () {
        it('可以透過字軌代碼查詢字軌資訊', function () {
            // $this->mockAlphanumericCodeSearchSuccess([
            //     'Result' => [
            //         [
            //             'TrackCode' => '00455ujp8',
            //             'Year' => 113,
            //             'Prefix' => 'AA',
            //             'StartNumber' => '00000001',
            //             'EndNumber' => '99999999',
            //             'UsedQuantity' => '1500',
            //             'Status' => '1',
            //         ],
            //     ],
            //     'TotalCount' => 1,
            // ]);

            // $result = $this->factory
            //     ->alphanumericCode()
            //     ->find('00455ujp8');

            // expect($result)->toBeArray()
            //     ->and($result['Result'])->toHaveCount(1)
            //     ->and($result['Result'][0]['TrackCode'])->toBe('00455ujp8')
            //     ->and($result['Result'][0]['UsedQuantity'])->toBe('1500');
        })->todo();

        it('可以透過年度查詢字軌列表', function () {
            // $this->mockAlphanumericCodeSearchSuccess([
            //     'Result' => [
            //         ['TrackCode' => '00455ujp8', 'Prefix' => 'AA', 'Year' => 113],
            //         ['TrackCode' => '00455ujp9', 'Prefix' => 'BB', 'Year' => 113],
            //         ['TrackCode' => '00455ujq0', 'Prefix' => 'CC', 'Year' => 113],
            //     ],
            //     'TotalCount' => 3,
            // ]);

            // $result = $this->factory
            //     ->alphanumericCode()
            //     ->where('year', 113)
            //     ->get();

            // expect($result['Result'])->toHaveCount(3)
            //     ->and($result['TotalCount'])->toBe(3);

            // foreach ($result['Result'] as $track) {
            //     expect($track['Year'])->toBe(113);
            // }
        })->todo();

        it('可以透過字軌前置碼查詢', function () {
            // $this->mockAlphanumericCodeSearchSuccess([
            //     'Result' => [
            //         ['TrackCode' => '00455ujp8', 'Prefix' => 'AA', 'Status' => '1'],
            //     ],
            // ]);

            // $result = $this->factory
            //     ->alphanumericCode()
            //     ->where('code', 'AA')
            //     ->get();

            // expect($result['Result'][0]['Prefix'])->toBe('AA');
        })->todo();
    });

    describe('字軌管理功能', function () {
        it('可以停用字軌', function () {
            // $this->mockAlphanumericCodeManageSuccess([
            //     'TrackCode' => '00455ujp8',
            //     'Status' => 0,
            //     'Message' => '字軌已停用',
            // ]);

            // $result = $this->factory
            //     ->alphanumericCode()
            //     ->withNo('00455ujp8')
            //     ->withYear(113)
            //     ->disable();

            // expect($result)->toBeArray()
            //     ->and($result['TrackCode'])->toBe('00455ujp8')
            //     ->and($result['Status'])->toBe(0);
        })->todo();

        it('可以啟用字軌', function () {
            // $this->mockAlphanumericCodeManageSuccess([
            //     'TrackCode' => '00455ujp8',
            //     'Status' => 1,
            //     'Message' => '字軌已啟用',
            // ]);

            // $result = $this->factory
            //     ->alphanumericCode()
            //     ->withNo('00455ujp8')
            //     ->withYear(113)
            //     ->enable();

            // expect($result)->toBeArray()
            //     ->and($result['TrackCode'])->toBe('00455ujp8')
            //     ->and($result['Status'])->toBe(1);
        })->todo();

        it('可以查詢字軌使用狀況', function () {
            // $this->mockAlphanumericCodeManageSuccess([
            //     'TrackCode' => '00455ujp8',
            //     'TotalQuantity' => '99999999',
            //     'UsedQuantity' => '15000',
            //     'RemainingQuantity' => '99984999',
            // ]);

            // $result = $this->factory
            //     ->alphanumericCode()
            //     ->find('00455ujp8');

            // expect($result)->toBeArray()
            //     ->and($result['TotalQuantity'])->toBe('99999999')
            //     ->and($result['UsedQuantity'])->toBe('15000')
            //     ->and($result['RemainingQuantity'])->toBe('99984999');
        })->todo();
    });
});
