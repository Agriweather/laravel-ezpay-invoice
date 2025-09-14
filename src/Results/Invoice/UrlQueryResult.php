<?php

namespace Agriweather\EzPayInvoice\Results\Invoice;

use Agriweather\EzPayInvoice\Results\Result;

final class UrlQueryResult extends Result
{
    private string $url;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->url = $data['Result'];
        $this->result = ['Url' => $this->url];
    }

    public function url(): string
    {
        return $this->url;
    }
}
