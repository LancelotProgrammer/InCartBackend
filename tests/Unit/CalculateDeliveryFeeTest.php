<?php

function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
{
    $earthRadius = 6371; // km

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat / 2) * sin($dLat / 2) +
        cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
        sin($dLon / 2) * sin($dLon / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $earthRadius * $c;
}

function calculateDeliveryFee(object $userLocation, object $branchLocation): float
{
    $distance = haversineDistance(
        $userLocation->lat,
        $userLocation->lng,
        $branchLocation->lat,
        $branchLocation->lng,
    );

    // Base fee $1 + $0.5 per km
    return round(1 + ($distance * 0.5), 2);
}

it('calculates delivery fee based on distance', function () {
    $branchLocation = (object) [
        'lat' => 40.7128,
        'lng' => -74.0060, // New York
    ];

    $userLocation = (object) [
        'lat' => 40.730610,
        'lng' => -73.935242, // NYC nearby
    ];

    $fee = calculateDeliveryFee($userLocation, $branchLocation);

    // Distance is about 8.4 km → fee = 1 + (8.4 * 0.5) = 5.2
    expect($fee)->toBe(5.2);
});

it('calculates minimum delivery fee when distance is zero', function () {
    $branchLocation = (object) [
        'lat' => 40.7128,
        'lng' => -74.0060,
    ];

    $userLocation = (object) [
        'lat' => 40.7128,
        'lng' => -74.0060,
    ];

    $fee = calculateDeliveryFee($userLocation, $branchLocation);

    // Distance 0 km → minimum fee $1
    expect($fee)->toBe(1);
});
