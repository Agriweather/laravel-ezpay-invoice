<?php

namespace Agriweather\EzpayInvoice\Builders;

use Agriweather\EzpayInvoice\Enums\TaxType;
use Agriweather\EzpayInvoice\Results\InvoiceCreateResult;

class InvoiceCreateBuilder extends Builder
{
    protected function boot(): void
    {
        $this->crypto->setHashKey($this->config['merchant_hash_key']);
        $this->crypto->setHashIv($this->config['merchant_hash_iv']);

        $this->postData = [
            'RespondType' => 'JSON',
            'Version' => '1.5',
            'TimeStamp' => time(),
        ];
    }

    public function withOrder(string $orderNo): self
    {
        $this->postData['MerchantOrderNo'] = $orderNo;

        return $this;
    }

    public function forConsumer(string $consumerName): self
    {
        $this->postData['BuyerName'] = $consumerName;

        return $this;
    }

    public function withEmail(string $email): self
    {
        $this->postData['BuyerEmail'] = $email;

        return $this;
    }

    public function withAddress(string $address): self
    {
        $this->postData['BuyerAddress'] = $address;

        return $this;
    }

    public function withItem(string $name, int $quantity, string $unit, int $price, int $amount): self
    {
        $this->items['ItemName'][] = $name;
        $this->items['ItemCount'][] = (string) $quantity;
        $this->items['ItemUnit'][] = $unit;
        $this->items['ItemPrice'][] = (string) $price;
        $this->items['ItemAmount'][] = (string) $amount;

        return $this;
    }

    public function withTax(TaxType $taxType, int $rate): self
    {
        $this->postData['TaxType'] = (string) $taxType->value;
        $this->postData['TaxRate'] = (string) $rate;

        return $this;
    }

    public function withAmount(?int $amount = null, ?int $taxAmount = null, ?int $totalAmount = null): self
    {
        if (! is_null($amount)) {
            $this->postData['Amount'] = (string) $amount;
        }

        if (! is_null($taxAmount)) {
            $this->postData['TaxAmount'] = (string) $taxAmount;
        }

        if (! is_null($totalAmount)) {
            $this->postData['TotalAmount'] = (string) $totalAmount;
        }

        return $this;
    }

    public function issue(): InvoiceCreateResult
    {
        $this->endpoint = '/Api/invoice_issue';

        $this->postData['ItemName'] = implode('|', $this->items['ItemName'] ?? []);
        $this->postData['ItemCount'] = implode('|', $this->items['ItemCount'] ?? []);
        $this->postData['ItemUnit'] = implode('|', $this->items['ItemUnit'] ?? []);
        $this->postData['ItemPrice'] = implode('|', $this->items['ItemPrice'] ?? []);
        $this->postData['ItemAmount'] = implode('|', $this->items['ItemAmount'] ?? []);

        $this->formData = [
            'MerchantID_' => $this->config['merchant_id'],
            'PostData_' => $this->crypto->encryptPostData($this->postData),
        ];

        $result = new InvoiceCreateResult($this->sendRequest()->json());

        // TODO: verify check code

        return $result;
    }
}
