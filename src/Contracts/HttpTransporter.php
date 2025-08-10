<?php

namespace Agriweather\EzPayInvoice\Contracts;

use Illuminate\Http\Client\Response as ClientResponse;

interface HttpTransporter extends Transporter
{
    public function send(string $url, array $data): ClientResponse;
}
