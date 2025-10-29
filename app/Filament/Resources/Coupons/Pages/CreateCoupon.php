<?php

namespace App\Filament\Resources\Coupons\Pages;

use App\Enums\CouponType;
use App\Filament\Resources\Coupons\CouponResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateCoupon extends CreateRecord
{
    protected static string $resource = CouponResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $type = CouponType::from((int) $data['type']);

        $config = $type->transformConfig($data);

        foreach (array_keys($config) as $key) {
            unset($data[$key]);
        }

        $data['config'] = $config;

        return static::getModel()::create($data);
    }
}
