<?php

namespace Agriweather\EzpayInvoice\Results;

final class InvoiceQueryUrlResult extends Result
{
    protected string $url;

    public function __construct(array $data)
    {
        $this->status = $data['Status'] ?? '';
        $this->message = $data['Message'] ?? '';
        $this->url = $data['Result'];
    }

    public function url(): string
    {
        return $this->url;
    }
}
