<?php

namespace Agriweather\EzpayInvoice\Contracts;

use Illuminate\Http\Client\Response;

interface HttpSender extends Sender
{
    public function send(string $url, array $data): Response;
}
