<?php

arch()
    ->expect('Agriweather\EzpayInvoice')
    ->not->toUse(['die', 'dd', 'dump']);

arch('builders')
    ->expect('Agriweather\EzpayInvoice\Builders')
    ->toOnlyUse([
        'Illuminate\Http\Client\Factory',
        'Illuminate\Http\Client\Response',
        'Illuminate\Support\Traits\Conditionable',
        'Illuminate\Support\Traits\Tappable',
        'Agriweather\EzpayInvoice\Factory',
        'Agriweather\EzpayInvoice\Crypto\EzpayCrypto',
        'Agriweather\EzpayInvoice\Options',
    ]);

arch('contracts')
    ->expect('Agriweather\EzpayInvoice\Contracts')
    ->toBeInterfaces()
    ->toUseNothing();

arch('enums')
    ->expect('Agriweather\EzpayInvoice\Enums')
    ->toBeEnums();

arch('exceptions')
    ->expect('Agriweather\EzpayInvoice\Exceptions')
    ->toUseNothing();

arch('facades')
    ->expect('Agriweather\EzpayInvoice\Facades')
    ->toOnlyUse([
        'Illuminate\Support\Facades\Facade',
    ]);

arch('options')
    ->expect('Agriweather\EzpayInvoice\Options')
    ->toOnlyUse([
        'Agriweather\EzpayInvoice\Enums',
        'Carbon\Carbon',
    ]);

arch('results')
    ->expect('Agriweather\EzpayInvoice\Results')
    ->toOnlyUse([
        'Carbon\Carbon',
    ]);
