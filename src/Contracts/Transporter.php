<?php

namespace Agriweather\EzPayInvoice\Contracts;

interface Transporter
{
    public function send(string $url, array $data): mixed;
}
