<?php

namespace Agriweather\EzPayInvoice\Transporters;

use Agriweather\EzPayInvoice\Contracts\FormRedirectTransporter as FormRedirectTransporterContract;
use Illuminate\Http\Response;

class FormRedirectTransporter implements FormRedirectTransporterContract
{
    public function send(string $url, array $data): Response
    {
        return response(<<<HTML
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
    </head>

    <body>
        <form id="order-form" action="{$url}" method="post">
            <input type="hidden" name="MerchantID_" value="{$data['MerchantID_']}">
            <input type="hidden" name="PostData_" value="{$data['PostData_']}">
            <input type="submit">
        </form>

        <script>document.getElementById("order-form").submit();</script>
    </body>
</html>
HTML)->header('Content-Type', 'text/html');
    }
}
