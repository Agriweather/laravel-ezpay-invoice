<?php

namespace Agriweather\EzpayInvoice\Results;

abstract class Result
{
    protected string $status;

    protected string $message;

    protected array $result;

    public function __construct(array $data)
    {
        $this->status = $data['Status'] ?? '';
        $this->message = $data['Message'] ?? '';
        $this->result = is_array($data['Result'] ?? null)
            ? $data['Result']
            : json_decode($data['Result'] ?? '[]', true);
    }

    public function status(): string
    {
        return $this->status;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function result(): array
    {
        return $this->result;
    }

    public function isSuccess(): bool
    {
        return $this->status === 'SUCCESS';
    }

    public function errorCode(): ?string
    {
        return !$this->isSuccess() ? $this->status : null;
    }

    public function errorMessage(): ?string
    {
        return !$this->isSuccess() ? $this->message : null;
    }
}
