<?php

if (function_exists('arch')) {
    arch()
        ->expect('Agriweather\EzPayInvoice')
        ->not->toUse(['die', 'dd', 'dump']);

    arch('builders')
        ->expect('Agriweather\EzPayInvoice\Builders')
        ->toOnlyUse([
            'Illuminate\Http\Client\Factory',
            'Illuminate\Http\Client\Response',
            'Illuminate\Support\Traits\Conditionable',
            'Illuminate\Support\Traits\Tappable',
            'Agriweather\EzPayInvoice\Factory',
            'Agriweather\EzPayInvoice\Crypto\Crypto',
            'Agriweather\EzPayInvoice\Options',
        ]);

    arch('contracts')
        ->expect('Agriweather\EzPayInvoice\Contracts')
        ->toBeInterfaces()
        ->toUseNothing();

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
        ]);

    arch('results')
        ->expect('Agriweather\EzPayInvoice\Results')
        ->toOnlyUse([
            'Carbon\Carbon',
        ]);
}
