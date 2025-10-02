<?php

use App\Services\DistanceService;

beforeEach(function () {
    $this->boundary = json_decode(
        '[{"latitude": 21.24586234991347, "longitude": 38.97399902343751, "name": "bl"}, {"latitude": 21.879341082799023, "longitude": 38.97399902343751, "name": "tl"}, {"latitude": 21.879341082799023, "longitude": 39.32281494140626, "name": "tr"}, {"latitude": 21.24586234991347, "longitude": 39.32281494140626, "name": "br"}, {"latitude": 21.562601716356248, "longitude": 39.14840698242188, "name": "c"}]',
        true
    );
});

describe('Distance service test', function () {
    test('The distance service correctly identifies a point inside the rectangle', function () {
        $validated = [
            'latitude' => 21.58005,
            'longitude' => 39.17493,
        ];
        $isPointInsideRectangle = DistanceService::isPointInsideRectangle(
            [
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ],
            $this->boundary,
        );

        expect($isPointInsideRectangle)->toBeFalse();
    });
    test('The distance service correctly identifies a point outside the rectangle. The point is in the right', function () {
        $validated = [
            'latitude' => 21.60559,
            'longitude' => 39.69674,
        ];
        $isPointInsideRectangle = DistanceService::isPointInsideRectangle(
            [
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ],
            $this->boundary,
        );

        expect($isPointInsideRectangle)->toBeTrue();
    });
    test('The distance service correctly identifies a point outside the rectangle. The point is in the bottom', function () {
        $validated = [
            'latitude' => 20.95988,
            'longitude' => 39.30989,
        ];
        $isPointInsideRectangle = DistanceService::isPointInsideRectangle(
            [
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ],
            $this->boundary,
        );

        expect($isPointInsideRectangle)->toBeTrue();
    });
    test('The distance service correctly identifies a point outside the rectangle. The point is in the left', function () {
        $validated = [
            'latitude' => 21.58949,
            'longitude' => 38.50521,
        ];
        $isPointInsideRectangle = DistanceService::isPointInsideRectangle(
            [
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ],
            $this->boundary,
        );

        expect($isPointInsideRectangle)->toBeTrue();
    });
    test('The distance service correctly identifies a point outside the rectangle. The point is in the top', function () {
        $validated = [
            'latitude' => 22.17244,
            'longitude' => 39.06531,
        ];
        $isPointInsideRectangle = DistanceService::isPointInsideRectangle(
            [
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ],
            $this->boundary,
        );

        expect($isPointInsideRectangle)->toBeTrue();
    });
})->group('unit-test');
