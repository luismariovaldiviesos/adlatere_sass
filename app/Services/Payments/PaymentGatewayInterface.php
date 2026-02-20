<?php

namespace App\Services\Payments;

interface PaymentGatewayInterface
{
    /**
     * Prepare a payment transaction.
     *
     * @param float $amount
     * @param string $reference (Tenant ID or Order ID)
     * @param array $clientData (email, ci, phone, name)
     * @return array (status, paymentId, url, message)
     */
    public function preparePayment(float $amount, string $reference, array $clientData): array;
}
