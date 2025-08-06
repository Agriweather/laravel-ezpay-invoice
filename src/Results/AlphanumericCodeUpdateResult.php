<?php

namespace Agriweather\EzpayInvoice\Results;

final class AlphanumericCodeUpdateResult extends Result
{
    use Concerns\HasAlphanumericCode;
    use Concerns\HasCheckCode;
}
