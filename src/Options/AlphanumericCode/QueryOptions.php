<?php

namespace Agriweather\EzPayInvoice\Options\AlphanumericCode;

use Agriweather\EzPayInvoice\Enums\AlphanumericCodeStatus;
use Agriweather\EzPayInvoice\Enums\InvoiceTerm;
use Agriweather\EzPayInvoice\Options\Options;
use Carbon\Carbon;

class QueryOptions extends Options
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
