<?php

namespace App\Filament\Actions;

use App\Enums\DeliveryScheduledType;
use App\Exceptions\LogicalException;
use App\Filament\Components\SelectBranchComponent;
use App\Models\BranchProduct;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use App\Services\BranchSettingsService;
use App\Services\DistanceService;
use App\Services\OrderService;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class CreateOrderAction
{
    public static function configure(): Action
    {
        return Action::make('create')
            ->authorize('create')
            ->schema([
                Wizard::make([
                    Step::make('Branch')
                        ->schema(self::branchStepSchema()),
                    Step::make('Customer')
                        ->schema(self::customerStepSchema()),
                    Step::make('Order')
                        ->schema(self::orderStepSchema()),
                    Step::make('Delivery')
                        ->schema(self::deliveryStepSchema()),
                    Step::make('Billing')
                        ->schema(self::billingStepSchema()),
                ]),
            ])
            ->action(function (array $data) {
                try {
                    OrderService::managerCreate(
                        $data['address_id'],
                        $data['delivery_scheduled_type'],
                        $data['delivery_date'] ?? null,
                        $data['payment_method_id'],
                        $data['coupon'],
                        collect($data['cart'])
                            ->map(fn ($item) => [
                                'id' => $item['product_id'],
                                'quantity' => $item['quantity'],
                            ])
                            ->toArray(),
                        $data['notes'],
                        $data['branch_id'],
                        $data['customer_id'],
                    );
                    Notification::make()
                        ->title('Order created successfully')
                        ->success()
                        ->send();
                } catch (LogicalException $e) {
                    Notification::make()
                        ->title($e->getMessage())
                        ->body($e->getDetails())
                        ->warning()
                        ->send();
                }
            });
    }

    private static function branchStepSchema(): array
    {
        return [
            SelectBranchComponent::configure()->live(),
        ];
    }

    private static function customerStepSchema(): array
    {
        return [
            Select::make('customer_id')->label('Customer')
                ->required()
                ->searchable()
                ->getSearchResultsUsing(fn (string $search, Get $get): array => OrderService::getUsers($search, $get('branch_id')))
                ->getOptionLabelUsing(fn ($value): ?string => User::find($value)?->name)
                ->live(),
            Select::make('address_id')
                ->required()
                ->relationship(
                    'userAddress',
                    'title',
                    fn (Builder $query, Get $get) => $query->where('user_id', '=', $get('customer_id'))
                )
                ->rules([
                    fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                        try {
                            DistanceService::validate(
                                $get('branch_id'),
                                $value,
                            );
                        } catch (LogicalException $e) {
                            $fail($e->details);
                        }
                    },
                ]),
        ];
    }

    private static function orderStepSchema(): array
    {
        return [
            Repeater::make('cart')
                ->columns(2)
                ->schema(function () {
                    return [
                        Select::make('product_id')
                            ->required()
                            ->searchable()
                            ->getSearchResultsUsing(fn (string $search): array => Product::query()
                                ->whereRaw('LOWER(title) LIKE ?', ['%'.strtolower($search).'%'])
                                ->limit(50)
                                ->pluck('title', 'id')
                                ->all())
                            ->getOptionLabelUsing(fn ($value): ?string => Product::find($value)?->title)
                            ->distinct()
                            ->live(),
                        TextInput::make('quantity')
                            ->required()
                            ->integer()
                            ->minValue(function (Get $get) {
                                $branchProduct = BranchProduct::published()->where('branch_id', '=', $get('../../branch_id'))
                                    ->where('product_id', '=', $get('product_id'))
                                    ->first();

                                if ($branchProduct) {
                                    return $branchProduct->minimum_order_quantity;
                                } else {
                                    return 0;
                                }
                            })
                            ->maxValue(function (Get $get) {
                                $branchProduct = BranchProduct::published()->where('branch_id', '=', $get('../../branch_id'))
                                    ->where('product_id', '=', $get('product_id'))
                                    ->first();

                                if ($branchProduct) {
                                    return $branchProduct->maximum_order_quantity;
                                } else {
                                    return 0;
                                }
                            }),
                    ];
                })
                ->reorderable(false)
                ->required(),
            Textarea::make('notes'),
        ];
    }

    private static function deliveryStepSchema(): array
    {
        return [
            Select::make('delivery_scheduled_type')
                ->required()
                ->afterStateUpdated(function (Set $set) {
                    $set('delivery_date', null);
                })
                ->options(DeliveryScheduledType::class)
                ->live(),
            DateTimePicker::make('delivery_date')
                ->required(function (Get $get) {
                    return $get('delivery_scheduled_type') === DeliveryScheduledType::SCHEDULED;
                })
                ->disabled(function (Get $get) {
                    return $get('delivery_scheduled_type') === DeliveryScheduledType::IMMEDIATE;
                })
                ->minDate(now()->inApplicationTimezone())
                ->maxDate(function (Get $get) {
                    return now()->addDays(BranchSettingsService::getMaxScheduledDays($get('branch_id')))->inApplicationTimezone();
                }),
        ];
    }

    private static function billingStepSchema(): array
    {
        return [
            Select::make('payment_method_id')
                ->required()
                ->options(function (Get $get) {
                    return OrderService::getPaymentMethods($get('branch_id'))->pluck('title', 'id');
                }),
            TextInput::make('coupon')
                ->belowContent(Schema::between([
                    Action::make('validate')->action(function ($state, Get $get) {
                        $coupon = Coupon::published()->where('branch_id', '=', $get('branch_id'))->where('code', '=', $state)->first();
                        if (! $coupon) {
                            Notification::make()
                                ->title('Coupon is invalid')
                                ->warning()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Coupon is valid')
                                ->success()
                                ->send();
                        }
                    }),
                ])),
            Section::make('Price')
                ->afterHeader([
                    Action::make('calculate')
                        ->action(function (Get $get, Set $set) {

                            if ($get('payment_method_id') === null) {
                                Notification::make()
                                    ->title('Please select a payment method')
                                    ->warning()
                                    ->send();

                                return;
                            }

                            $orderBill = OrderService::userCreateBill(
                                $get('address_id'),
                                $get('delivery_scheduled_type') === DeliveryScheduledType::SCHEDULED ?
                                    DeliveryScheduledType::SCHEDULED->value :
                                    DeliveryScheduledType::IMMEDIATE->value,
                                $get('delivery_date') ?? null,
                                $get('payment_method_id'),
                                $get('coupon'),
                                collect($get('cart'))
                                    ->map(fn ($item) => [
                                        'id' => $item['product_id'],
                                        'quantity' => $item['quantity'],
                                    ])
                                    ->toArray(),
                                $get('notes'),
                                $get('branch_id'),
                                $get('customer_id'),
                            );
                            $set('subtotal', $orderBill['subtotal']);
                            $set('discount', $orderBill['discount']);
                            $set('delivery_fee', $orderBill['delivery_fee']);
                            $set('tax', $orderBill['tax']);
                            $set('total', $orderBill['total']);
                        }),
                ])
                ->columns(5)
                ->schema([
                    TextInput::make('subtotal')->disabled()->label('Cart Total'),
                    TextInput::make('discount')->disabled()->label('Discount'),
                    TextInput::make('delivery_fee')->disabled()->label('Delivery Fee'),
                    TextInput::make('tax')->disabled()->label('Tax'),
                    TextInput::make('total')->disabled()->label('Total'),
                ]),
        ];
    }
}
