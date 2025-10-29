<?php

namespace App\Enums;

use App\Exceptions\LogicalException;
use App\Models\Coupon;
use App\Models\Order;
use App\Services\CouponService;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\HtmlString;
use InvalidArgumentException;

enum CouponType: int implements HasLabel
{
    case TIMED = 1;

    public function getLabel(): string
    {
        return match ($this) {
            self::TIMED => 'Timed'
        };
    }

    public function getForm(): array
    {
        return match ($this) {
            self::TIMED => [
                Fieldset::make('Information')
                    ->columnSpan(2)
                    ->columns(3)
                    ->schema([
                        TextInput::make('value')
                            ->required()
                            ->numeric()
                            ->minValue(1),
                        DateTimePicker::make('start_date')
                            ->required()
                            ->minDate(now()),
                        DateTimePicker::make('end_date')
                            ->required()
                            ->minDate(fn(Get $get) => $get('start_date'))
                            ->after(fn(Get $get) => $get('start_date')),
                        TextInput::make('use_limit')
                            ->required()
                            ->numeric()
                            ->minValue(1),
                        TextInput::make('user_limit')
                            ->required()
                            ->numeric()
                            ->minValue(1),
                    ]),
                Fieldset::make('Description')
                    ->columnSpan(1)
                    ->columns(1)
                    ->schema([
                        TextEntry::make('config_description')
                            ->state(new HtmlString('
                            <ul style="margin:0; padding-left:18px; line-height:1.7;">
                                <li><strong>value</strong> -> <span>قيمة المبلغ المالي للخصم .</span></li>
                                <li><strong>start_date</strong> -> <span>تاريخ بداية صلاحية الكوبون.</span></li>
                                <li><strong>end_date</strong> -> <span>تاريخ انتهاء صلاحية الكوبون.</span></li>
                                <li><strong>use_limit</strong> -> <span>عدد المرات المسموح باستخدام الكوبون بشكل عام.</span></li>
                                <li><strong>user_limit</strong> -> <span>عدد المرات المسموح لكل مستخدم فردي أن يستفيد من الكوبون.</span></li>
                            </ul>
                        ')),
                    ]),
            ]
        };
    }

    public function getValidationRulesForApply(): array
    {
        return match ($this) {
            self::TIMED => [
                'value' => ['required', 'integer', 'min:1'],
                'start_date' => ['required', 'date'],
                'end_date' => ['required', 'date'],
                'use_limit' => ['required', 'integer', 'min:1'],
                'user_limit' => ['required', 'integer', 'min:1'],
            ],
        };
    }

    public function transformConfig(array $data): array
    {
        return match ($this) {
            self::TIMED => [
                'value' => $data['value'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'use_limit' => $data['use_limit'],
                'user_limit' => $data['user_limit'],
            ],
        };
    }

    public function calculateDiscount(CouponService $context, Coupon $coupon): float
    {
        return match ($this) {
            self::TIMED => $this->calculateTimedDiscount($context, $coupon),
        };
    }

    private function calculateTimedDiscount(CouponService $context, Coupon $coupon): float
    {
        $config = $coupon->config;

        $validator = Validator::make($config, $this->getValidationRulesForApply());
        if ($validator->fails()) {
            throw new InvalidArgumentException('Invalid coupon config: ' . $validator->errors()->first());
        }

        if (! empty($config['start_date']) && $context->time->lt(Carbon::parse($config['start_date']))) {
            throw new LogicalException('Coupon error', 'Coupon is not active yet.');
        }
        if (! empty($config['end_date']) && $context->time->gt(Carbon::parse($config['end_date']))) {
            throw new LogicalException('Coupon error', 'Coupon has expired.');
        }

        $totalUses = Order::where('coupon_id', $coupon->id)->count();
        $userUses = Order::where('coupon_id', $coupon->id)->where('customer_id', $context->userId)->count();

        // Check user limit first
        if (! empty($config['user_limit']) && $userUses >= $config['user_limit']) {
            throw new LogicalException('Coupon error', 'You have already used this coupon the maximum number of times for your account.');
        }
        // Check total use limit
        if (! empty($config['use_limit']) && $totalUses >= $config['use_limit']) {
            throw new LogicalException('Coupon error', 'This coupon has reached its maximum number of uses.');
        }
        // Check if using the coupon now would exceed total limit
        if (! empty($config['use_limit']) && $totalUses + 1 > $config['use_limit']) {
            throw new LogicalException('Coupon error', 'Using this coupon now would exceed the total allowed uses.');
        }
        // Optional: prevent user from “taking” another user’s slot if near limit
        if (! empty($config['use_limit']) && ! empty($config['user_limit'])) {
            $remainingUses = $config['use_limit'] - $totalUses;
            $remainingForUser = $config['user_limit'] - $userUses;
            if ($remainingUses <= 0 || $remainingForUser <= 0) {
                throw new LogicalException('Coupon error', 'Coupon cannot be used due to limit restrictions.');
            }
        }

        return (float) $config['value'];
    }
}
