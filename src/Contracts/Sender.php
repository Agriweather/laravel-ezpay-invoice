<?php

namespace Agriweather\EzPayInvoice\Contracts;

interface Sender
{
    public function send(string $url, array $data): mixed;
}
