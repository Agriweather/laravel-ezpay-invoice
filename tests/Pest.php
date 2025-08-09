<?php

if (function_exists('pest')) {
    pest()->extend(Agriweather\EzPayInvoice\Tests\TestCase::class)->in('Feature');
} else {
    // Fallback for Pest v1.x
    uses(Agriweather\EzPayInvoice\Tests\TestCase::class)->in('Feature');
}
