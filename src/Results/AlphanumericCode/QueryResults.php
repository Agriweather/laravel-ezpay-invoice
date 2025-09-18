<?php

namespace Agriweather\EzPayInvoice\Results\AlphanumericCode;

use Agriweather\EzPayInvoice\Results\Result;
use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * @implements \ArrayAccess<int, \Agriweather\EzPayInvoice\Results\AlphanumericCode\QueryResult>
 * @implements \IteratorAggregate<int, \Agriweather\EzPayInvoice\Results\AlphanumericCode\QueryResult>
 */
final class QueryResults extends Result implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * @var \Agriweather\EzPayInvoice\Results\AlphanumericCode\QueryResult[]
     */
    private array $results;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->data['Result'] = is_array($this->data['Result'] ?? null)
            ? $this->data['Result']
            : json_decode($this->data['Result'] ?? '[]', true);

        $this->results = array_map(function ($result) {
            return new QueryResult(['Result' => $result]);
        }, $this->data['Result'] ?? []);

        $this->result = [];
    }

    /**
     * @return \Agriweather\EzPayInvoice\Results\AlphanumericCode\QueryResult[]
     */
    public function results(): array
    {
        return $this->results;
    }

    public function count(): int
    {
        return count($this->results);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->results[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->results[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        // 禁止外部設定
    }

    public function offsetUnset(mixed $offset): void
    {
        // 禁止外部設定
    }

    /**
     * @return \ArrayIterator<int, \Agriweather\EzPayInvoice\Results\AlphanumericCode\QueryResult>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->results);
    }
}
