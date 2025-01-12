<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class PhonePeHelper
{
    private $hostUrl;
    private $merchantId;
    private $saltKey;
    private $saltIndex;

    public function __construct()
    {
        $this->hostUrl = env('PHONE_PE_HOST_URL', 'https://api-preprod.phonepe.com/apis/hermes');
        $this->merchantId = env('MERCENT_ID', 'PGTESTPAYUAT');
        $this->saltKey = env('SALT_KEY', '099eb0cd-02cf-4e2a-8aca-3e6c6aff0399');
        $this->saltIndex = env('SALT_INDEX', 1);
    }

    public function initiatePayment($payload)
    {
        $encodedPayload = base64_encode(json_encode($payload));
        $checksum = hash('sha256', $encodedPayload . "/pg/v1/pay" . $this->saltKey);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-VERIFY' => $checksum . '###' . $this->saltIndex,
        ])->post("{$this->hostUrl}/pg/v1/pay", [
            'request' => $encodedPayload,
        ]);

        return $response->json();
    }

    public function getPaymentStatus($transactionId)
    {
        $checksum = hash('sha256', $this->merchantId . "/" . $transactionId . "/pg/v1/status" . $this->saltKey);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-VERIFY' => $checksum . '###' . $this->saltIndex,
        ])->get("{$this->hostUrl}/pg/v1/status", [
            'merchantId' => $this->merchantId,
            'transactionId' => $transactionId,
        ]);

        return $response->json();
    }
}
