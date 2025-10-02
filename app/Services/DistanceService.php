<?php

namespace App\Services;

class DistanceService
{
    public static function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);

        return $earthRadius * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    public static function isPointInsideRectangle(array $point, array $rectangle): bool
    {
        $bl = null;
        $tr = null;
        foreach ($rectangle as $corner) {
            if ($corner['name'] === 'bl') {
                $bl = $corner;
            } elseif ($corner['name'] === 'tr') {
                $tr = $corner;
            }
        }
        if (! $bl || ! $tr) {
            return false;
        }

        return ! ($point['latitude'] >= $bl['latitude'] && $point['latitude'] <= $tr['latitude'] && $point['longitude'] >= $bl['longitude'] && $point['longitude'] <= $tr['longitude']);
    }
}
