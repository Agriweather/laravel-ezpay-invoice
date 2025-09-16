<?php

// Architecture testing is available in PestPHP v2.0+
if (function_exists('arch')) {
    arch('main')
        ->expect('Agriweather\EzPayInvoice')
        ->not->toUse(['die', 'dd', 'dump'])
        ->ignoring('Agriweather\EzPayInvoice\Builders\Concerns\Dumpable');

    arch('attributes')
        ->expect('Agriweather\EzPayInvoice\Attributes')
        ->toUseNothing();

    $buildersIgnored = [
        'Agriweather\EzPayInvoice\Builders\Builder',
        'Agriweather\EzPayInvoice\Builders\Concerns',
    ];

    // 注意：`ignoring()` 方法目前只會對上一行定義的期望有效
    // 因此需要對每個期望都呼叫一次 `ignoring()` 方法
    arch('builders')
        ->expect('Agriweather\EzPayInvoice\Builders')
        ->toBeFinal()
        ->ignoring($buildersIgnored)
        ->toHaveAttribute('Agriweather\EzPayInvoice\Attributes\Resource')
        ->ignoring($buildersIgnored);

    arch('contracts')
        ->expect('Agriweather\EzPayInvoice\Contracts')
        ->toBeInterfaces();

    arch('enums')
        ->expect('Agriweather\EzPayInvoice\Enums')
        ->toBeEnums();

    arch('exceptions')
        ->expect('Agriweather\EzPayInvoice\Exceptions')
        ->toUseNothing();

    arch('facades')
        ->expect('Agriweather\EzPayInvoice\Facades')
        ->toOnlyUse([
            'Illuminate\Support\Facades\Facade',
        ]);

    arch('options')
        ->expect('Agriweather\EzPayInvoice\Options')
        ->toBeFinal()
        ->ignoring('Agriweather\EzPayInvoice\Options\Options');

    arch('resources')
        ->expect('Agriweather\EzPayInvoice\Resources')
        ->toBeFinal()
        ->ignoring('Agriweather\EzPayInvoice\Resources\Concerns');

    arch('results')
        ->expect('Agriweather\EzPayInvoice\Results')
        ->toBeFinal()
        ->ignoring([
            'Agriweather\EzPayInvoice\Results\Concerns',
            'Agriweather\EzPayInvoice\Results\Result',
        ]);
}
