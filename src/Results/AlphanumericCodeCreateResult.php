<?php

namespace Agriweather\EzPayInvoice\Results;

final class AlphanumericCodeCreateResult extends Result
{
    use Concerns\HasAlphanumericCode;
    use Concerns\HasCheckCode;
}
