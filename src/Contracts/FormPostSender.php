<?php

namespace Agriweather\EzpayInvoice\Contracts;

use Illuminate\Http\Response;

interface FormPostSender extends Sender
{
    public function send(string $url, array $data): Response;
}
