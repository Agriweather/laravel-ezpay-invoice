<?php

arch()
    ->expect('Agriweather\EzpayInvoice')
    ->not->toUse(['die', 'dd', 'dump']);

arch('builders')
    ->expect('Agriweather\EzpayInvoice\Builders')
    ->toOnlyUse([
        'Illuminate\Http\Client\Response',
        'Illuminate\Support\Facades\Http',
        'Illuminate\Support\Traits\Conditionable',
        'Illuminate\Support\Traits\Tappable',
        'Agriweather\EzpayInvoice\Factory',
        'Agriweather\EzpayInvoice\Crypto\EzpayCrypto',
        'Agriweather\EzpayInvoice\Enums',
        'Agriweather\EzpayInvoice\Results',
    ]);

arch('enums')
    ->expect('Agriweather\EzpayInvoice\Enums')
    ->toBeEnums();

arch('facades')
    ->expect('Agriweather\EzpayInvoice\Facades')
    ->toOnlyUse([
        'Illuminate\Support\Facades\Facade',
    ]);

arch('results')
    ->expect('Agriweather\EzpayInvoice\Results')
    ->toOnlyUse([
        'Carbon\Carbon',
    ]);
