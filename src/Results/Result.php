<?php

namespace Agriweather\EzPayInvoice\Results;

use Illuminate\Contracts\Support\Arrayable;

abstract class Result implements Arrayable
{
    protected array $data;

    protected array $result;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->data['Result'] = is_array($this->data['Result'] ?? null)
            ? $this->data['Result']
            : json_decode($this->data['Result'] ?? '[]', true);

        $this->result = $this->data['Result'] ?? [];
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public function result(): array
    {
        return $this->result;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
