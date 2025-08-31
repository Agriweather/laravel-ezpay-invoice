<?php

namespace Agriweather\EzPayInvoice\Results;

use Illuminate\Contracts\Support\Arrayable;

abstract class Result implements Arrayable
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

    public function toArray(): array
    {
        return $this->result();
    }
}
