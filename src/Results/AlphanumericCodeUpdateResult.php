<?php

namespace Agriweather\EzPayInvoice\Results;

final class AlphanumericCodeUpdateResult extends Result
{
    use Concerns\HasAlphanumericCode;
    use Concerns\HasCheckCode;
}
