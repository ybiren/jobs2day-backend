<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TranzilaPaymentService
{
    protected $url;
    protected $terminal;

    public function __construct()
    {
        $this->url = env('TRANZILA_URL', 'https://secure5.tranzila.com/cgi-bin/tranzila71u.cgi');
        $this->terminal = env('TRANZILA_TERMINAL'); // Use TRANZILA_TERMINAL instead of TRANZILA_TERMINAL_NAME
    }

    public function processPayment($cardNumber, $cvv, $expDate, $amount)
    {
        $paymentData = [
            "terminal" => $this->terminal, // Updated key
            "txn_currency_code" => "ILS",
            "txn_type" => "debit",
            "reference_txn_id" => null,
            "authorization_number" => null,
            "card_number" => $cardNumber,
            "expire_month" => substr($expDate, 0, 2),
            "expire_year" => substr($expDate, 2, 2),
            "payment_plan" => 1,
            "items" => [
                [
                    "code" => "1",
                    "name" => "Service Payment",
                    "unit_price" => $amount,
                    "type" => "I",
                    "units_number" => 1,
                    "unit_type" => 1,
                    "price_type" => "G",
                    "currency_code" => "ILS",
                    "to_txn_currency_exchange_rate" => 1,
                    "attributes" => [
                        [
                            "language" => "hebrew",
                            "name" => "attribute name",
                            "value" => "attribute value"
                        ]
                    ]
                ]
            ],
            "response_language" => "english",
            "user_defined_fields" => [
                [
                    "name" => "company",
                    "value" => "test 222"
                ]
            ]
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post($this->url, $paymentData);

        return $response->json();
    }
}
