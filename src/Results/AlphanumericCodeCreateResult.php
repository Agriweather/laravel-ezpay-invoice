<?php

namespace Agriweather\EzpayInvoice\Results;

final class AlphanumericCodeCreateResult extends Result
{
    use Concerns\HasAlphanumericCode;
    use Concerns\HasCheckCode;
}
