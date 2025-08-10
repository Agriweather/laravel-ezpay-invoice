<?php

namespace Agriweather\EzPayInvoice\Results\Invoice;

use Agriweather\EzPayInvoice\Results\Result;

final class UrlQueryResult extends Result
{
    protected string $url;

    public function __construct(array $data)
    {
        $this->url = $data['Result'];
    }

    public function url(): string
    {
        return $this->url;
    }
}
