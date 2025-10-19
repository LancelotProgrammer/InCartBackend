<?php

namespace App\Services;

class PriceService
{
    public static function calculateTaxAmount(float $subtotal, float $serviceFee, float $taxRate): float
    {
        return ($serviceFee + $subtotal) * ($taxRate / 100);
    }

    public static function calculateTotalPrice(
        float $subtotal,
        float $discount,
        float $deliveryFee,
        float $serviceFee,
        float $taxRate
    ): float {
        $taxAmount = self::calculateTaxAmount($subtotal, $serviceFee, $taxRate);

        return round(
            $subtotal - $discount + $deliveryFee + $serviceFee + $taxAmount,
            2
        );
    }
}
