<?php

namespace App\Filament\Filters;

use App\Enums\DeliveryScheduledType;
use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class OrderTableFilter
{
    public static function configure(): array
    {
        return [
            Filter::make('is_today')->query(fn (Builder $query): Builder => $query->whereDate('delivery_date', '=', now())),
            Filter::make('created_at')
                ->schema([
                    DatePicker::make('created_from'),
                    DatePicker::make('created_until')->after('created_from'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['created_from'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                        )
                        ->when(
                            $data['created_until'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                        );
                }),
            SelectFilter::make('order_status')->options(OrderStatus::class),
            SelectFilter::make('payment_status')->options(PaymentStatus::class),
            SelectFilter::make('delivery_status')->options(DeliveryStatus::class),
            SelectFilter::make('delivery_scheduled_type')->options(DeliveryScheduledType::class),
        ];
    }
}
