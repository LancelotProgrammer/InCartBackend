<?php

namespace App\Traits;

use App\Exceptions\LogicalException;

trait CanValidatePaymentGatewayData
{
    private function validateWalletPayment(array $payload): array
    {
        $required = ['type', 'token'];
        foreach ($required as $field) {
            if (empty($payload[$field])) {
                throw new LogicalException("Card payment missing field: {$field}");
            }
        }

        if (!is_string($payload['token']) || strlen($payload['token']) < 20) {
            throw new LogicalException("Invalid wallet token format.");
        }

        return [
            'type' => $payload['type'],
            'token' => $payload['token'],
        ];
    }

    private function validateStcPay(array $payload): array
    {
        $required = ['type', 'mobile'];
        foreach ($required as $field) {
            if (empty($payload[$field])) {
                throw new LogicalException("Card payment missing field: {$field}");
            }
        }

        if (!preg_match('/^05\d{8}$/', $payload['mobile'])) {
            throw new LogicalException("Invalid STC Pay mobile number format.");
        }

        return [
            'type' => 'stcpay',
            'mobile' => $payload['mobile'],
        ];
    }

    private function validateCardPayment(array $payload): array
    {
        $required = ['type', 'name', 'number', 'month', 'year', 'cvc'];
        foreach ($required as $field) {
            if (empty($payload[$field])) {
                throw new LogicalException("Card payment missing field: {$field}");
            }
        }

        if (!preg_match('/^\d{13,19}$/', $payload['number'])) {
            throw new LogicalException("Invalid card number.");
        }
        if ($payload['month'] < 1 || $payload['month'] > 12) {
            throw new LogicalException("Invalid card expiry month.");
        }
        if (!preg_match('/^\d{2,4}$/', (string)$payload['year'])) {
            throw new LogicalException("Invalid card expiry year.");
        }
        if (!preg_match('/^\d{3,4}$/', $payload['cvc'])) {
            throw new LogicalException("Invalid CVC.");
        }

        return [
            'type' => 'creditcard',
            'name' => $payload['name'],
            'number' => $payload['number'],
            'month' => $payload['month'],
            'year' => $payload['year'],
            'cvc' => $payload['cvc'],
            'save_token' => false,
        ];
    }
}
