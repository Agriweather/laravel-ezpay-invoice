<?php

namespace Agriweather\EzPayInvoice\Results;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

abstract class Result implements Arrayable, JsonSerializable
{
    protected array $result;

    public function __construct(
        protected array $data
    ) {
        $this->data['Result'] = is_array($this->data['Result'] ?? null)
            ? $this->data['Result']
            : json_decode($this->data['Result'] ?? '[]', true);

        $this->result = $this->data['Result'] ?? [];
    }

    public static function make(array $data): static
    {
        /** @phpstan-ignore-next-line */
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

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
