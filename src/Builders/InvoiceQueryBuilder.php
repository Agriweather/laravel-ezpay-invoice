<?php

namespace Agriweather\EzpayInvoice\Builders;

use Carbon\Carbon;

class InvoiceQueryBuilder extends Builder
{
    protected function boot(): void
    {
        $this->crypto->setHashKey($this->factory->config('merchant_hash_key'));
        $this->crypto->setHashIv($this->factory->config('merchant_hash_iv'));

        $this->postData = [
            'RespondType' => 'JSON',
            'Version' => '1.3',
            'TimeStamp' => Carbon::now()->timestamp,
        ];
    }
}
