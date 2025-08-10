<?php

namespace Agriweather\EzPayInvoice\Results\AlphanumericCode;

use Agriweather\EzPayInvoice\Results\Concerns;
use Agriweather\EzPayInvoice\Results\Result;

final class CreateResult extends Result
{
    use Concerns\HasAlphanumericCode;
    use Concerns\HasCheckCode;
}
