<?php

namespace Agriweather\EzPayInvoice\Options;

use Agriweather\EzPayInvoice\Enums\InvoiceTerm;
use Agriweather\EzPayInvoice\Enums\InvoiceType;
use Carbon\Carbon;

class AlphanumericCodeCreateOptions extends Options
{
    public string $companyId = '';

    public int $year = 0;

    public InvoiceTerm $term = InvoiceTerm::JAN_FEB;

    public string $alphabeticLetter = '';

    public string $startNumber = '';

    public string $endNumber = '';

    public InvoiceType $type = InvoiceType::GENERAL;

    public function toArray()
    {
        return [
            'CompanyID_' => $this->companyId,
            'PostData_' => array_filter([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'Year' => (string) $this->year,
                'Term' => (string) $this->term->value,
                'AphabeticLetter' => $this->alphabeticLetter,
                'StartNumber' => $this->startNumber,
                'EndNumber' => $this->endNumber,
                'Type' => (string) $this->type->value,
            ], fn ($value) => ! is_null($value)),
        ];
    }
}
