<?php

namespace Agriweather\EzPayInvoice\Options\AlphanumericCode;

use Agriweather\EzPayInvoice\Enums\Invoice\InvoiceTerm;
use Agriweather\EzPayInvoice\Enums\Invoice\InvoiceType;
use Agriweather\EzPayInvoice\Options\Options;
use Carbon\Carbon;

final class CreateOptions extends Options
{
    public string $companyId = '';

    public int $year = 0;

    public InvoiceTerm $term = InvoiceTerm::JAN_FEB;

    public string $alphanumericCode = '';

    public string $startNumber = '';

    public string $endNumber = '';

    public InvoiceType $type = InvoiceType::GENERAL;

    public function toArray()
    {
        return [
            'CompanyID_' => $this->companyId,
            'PostData_' => [
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'Year' => (string) $this->year,
                'Term' => (string) $this->term->value,
                'AphabeticLetter' => $this->alphanumericCode,
                'StartNumber' => $this->startNumber,
                'EndNumber' => $this->endNumber,
                'Type' => (string) $this->type->value,
            ],
        ];
    }
}
