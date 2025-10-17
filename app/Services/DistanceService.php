<?php

namespace App\Services;

use App\Exceptions\LogicalException;
use App\Models\Branch;
use App\Models\UserAddress;

class DistanceService
{
    public static function validate(int $branchId, int $addressId): float
    {
        $branch = Branch::where('id', $branchId)->first();
        if (! $branch) {
            throw new LogicalException('Branch is invalid', 'The selected branch does not exist.');
        }

        $address = UserAddress::where('id', $addressId)->first();
        if (! $address) {
            throw new LogicalException('User address is invalid', 'The address does not exist or does not belong to you.');
        }

        $distance = self::haversineDistance(
            $branch->latitude,
            $branch->longitude,
            $address->latitude,
            $address->longitude
        );

        $min = SettingsService::getMinDistance();
        $max = SettingsService::getMaxDistance();

        if ($distance < $min || $distance > $max) {
            throw new LogicalException(
                'Destination is invalid',
                "The total destination is {$distance} km, which is outside the allowed range of {$min} km to {$max} km."
            );
        }

        return $distance;
    }

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
