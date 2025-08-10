<?php

namespace Agriweather\EzPayInvoice\Results\AlphanumericCode;

use Agriweather\EzPayInvoice\Results\Concerns;
use Agriweather\EzPayInvoice\Results\Result;

final class UpdateResult extends Result
{
    use Concerns\HasAlphanumericCode;
    use Concerns\HasCheckCode;
}
