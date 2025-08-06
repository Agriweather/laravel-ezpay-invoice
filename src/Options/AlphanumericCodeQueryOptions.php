<?php

namespace Agriweather\EzpayInvoice\Options;

use Agriweather\EzpayInvoice\Enums\AlphanumericCodeStatus;
use Agriweather\EzpayInvoice\Enums\InvoiceTerm;
use Carbon\Carbon;

class AlphanumericCodeQueryOptions extends Options
{
    public string $companyId = '';

    public ?string $managementNo = null;

    public int $year = 0;

    public ?InvoiceTerm $term = null;

    public ?AlphanumericCodeStatus $status = null;

    public function toArray()
    {
        return [
            'CompanyID_' => $this->companyId,
            'PostData_' => array_filter([
                'RespondType' => 'JSON',
                'Version' => '1.0',
                'TimeStamp' => Carbon::now()->timestamp,
                'ManagementNo' => $this->managementNo,
                'Year' => (string) $this->year,
                'Term' => isset($this->term) ? (string) $this->term->value : null,
                'Flag' => isset($this->status) ? (string) $this->status->value : null,
            ], fn ($value) => ! is_null($value)),
        ];
    }
}
