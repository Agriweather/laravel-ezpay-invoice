<?php

namespace Agriweather\EzpayInvoice\Senders;

use Agriweather\EzpayInvoice\Contracts\FormPostSender as FormPostSenderContract;
use Illuminate\Http\Response;

class FormPostSender implements FormPostSenderContract
{
    public function __construct()
    {
        //
    }

    public function send(string $url, array $data): Response
    {
        return response([], 200);
    }
}
