<?php

namespace Agriweather\EzpayInvoice\Contracts;

interface Sender
{
    public function send(string $url, array $data): mixed;
}
