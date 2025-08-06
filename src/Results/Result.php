<?php

namespace Agriweather\EzpayInvoice\Results;

abstract class Result
{
    protected array $result;

    public function __construct(array $data)
    {
        $this->result = is_array($data['Result'] ?? null)
            ? $data['Result']
            : json_decode($data['Result'] ?? '[]', true);
    }

    public function result(): array
    {
        return $this->result;
    }
}
