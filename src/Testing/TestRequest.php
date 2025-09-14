<?php

namespace Agriweather\EzPayInvoice\Testing;

use Agriweather\EzPayInvoice\Options\Options;

final class TestRequest
{
    public function __construct(
        private string $resource,
        private ?string $action,
        private Options $options
    ) {
        //
    }

    public function resource(): string
    {
        return $this->resource;
    }

    public function action(): ?string
    {
        return $this->action;
    }

    public function options(): Options
    {
        return $this->options;
    }
}
