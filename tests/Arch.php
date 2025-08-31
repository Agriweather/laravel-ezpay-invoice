<?php

// Architecture testing is available in PestPHP v2.0+
if (function_exists('arch')) {
    arch()
        ->expect('Agriweather\EzPayInvoice')
        ->not->toUse(['die', 'dd', 'dump']);

    arch('builders')
        ->expect('Agriweather\EzPayInvoice\Builders')
        ->toOnlyUse([
            'Agriweather\EzPayInvoice\Contracts',
            'Agriweather\EzPayInvoice\Crypto\Crypto',
            'Agriweather\EzPayInvoice\Enums',
            'Agriweather\EzPayInvoice\Exceptions',
            'Agriweather\EzPayInvoice\Factory',
            'Agriweather\EzPayInvoice\Options',
            'Agriweather\EzPayInvoice\Results',
            'Illuminate\Http\Response',
            'Illuminate\Support\Traits\Conditionable',
            'Illuminate\Support\Traits\Tappable',
        ]);

    arch('contracts')
        ->expect('Agriweather\EzPayInvoice\Contracts')
        ->toBeInterfaces()
        ->toOnlyUse([
            'Illuminate\Http\Client\Response',
            'Illuminate\Http\Response',
        ]);

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
        ->toOnlyUse([
            'Agriweather\EzPayInvoice\Enums',
            'Carbon\Carbon',
            'Illuminate\Contracts\Support\Arrayable',
        ]);

    arch('results')
        ->expect('Agriweather\EzPayInvoice\Results')
        ->toOnlyUse([
            'Agriweather\EzPayInvoice\Enums',
            'Agriweather\EzPayInvoice\Contracts',
            'Carbon\Carbon',
        ]);
}
