<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Exceptions\LogicalException;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Branch;
use App\Models\BranchProduct;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Models\UserAddress;
use App\Services\DistanceService;
use App\Services\OrderService;
use App\Support\OrderPayload;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->schema([
                    Wizard::make([
                        Step::make('Branch')
                            ->schema([
                                Select::make('branch_id')->relationship('branch', 'title')->required()->live(),
                            ]),
                        Step::make('Customer')
                            ->schema([
                                Select::make('customer_id')
                                    ->options(
                                        User::where('role_id', '=', Role::where('code', '=', 'user')->first()->id)
                                            ->pluck('name', 'id')
                                    )
                                    ->searchable()
                                    ->required()
                                    ->live(),
                                Select::make('address_id')
                                    ->relationship(
                                        'userAddress',
                                        'title',
                                        fn(Builder $query, Get $get) => $query->where('user_id', '=', $get('customer_id'))
                                    )
                                    ->required()
                                    ->rules([
                                        fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                            $branch = Branch::where('id', '=', $get('branch_id'))->first();
                                            $address = UserAddress::where('id', '=', $value)->first();
                                            $distance = DistanceService::haversineDistance(
                                                $branch->latitude,
                                                $branch->longitude,
                                                $address->latitude,
                                                $address->longitude
                                            );
                                            // TODO: get from settings
                                            if (
                                                $distance < 0.2
                                                || $distance > 100
                                            ) {
                                                $fail("The total destination is {$distance} km, which is outside the allowed range of 0.2 km to 100 km.");
                                            }
                                        },
                                    ]),
                            ]),
                        Step::make('Order')
                            ->schema([
                                Repeater::make('cart')
                                    ->columns(2)
                                    ->schema(function () {
                                        return [
                                            Select::make('product_id')->options(Product::query()->pluck('title', 'id'))->distinct()->live(),
                                            TextInput::make('quantity')
                                                ->numeric()
                                                ->minValue(function (Get $get) {
                                                    return BranchProduct::where('branch_id', '=', $get('../../branch_id'))
                                                        ->where('product_id', '=', $get('product_id'))
                                                        ->first()->minimum_order_quantity;
                                                })
                                                ->maxValue(function (Get $get) {
                                                    $branchProduct = BranchProduct::where('branch_id', '=', $get('../../branch_id'))
                                                        ->where('product_id', '=', $get('product_id'))
                                                        ->first();
                                                    return $branchProduct?->maximum_order_quantity > $branchProduct?->quantity ? $branchProduct?->quantity : $branchProduct?->maximum_order_quantity;
                                                }),
                                        ];
                                    })
                                    ->reorderable(false)
                                    ->required(),
                                Textarea::make('notes'),
                            ]),
                        Step::make('Delivery')
                            ->schema([
                                DateTimePicker::make('delivery_date')->rules(['after_or_equal:today']),
                            ]),
                        Step::make('Billing')
                            ->schema([
                                Select::make('payment_method_id')
                                    ->relationship(
                                        'paymentMethod',
                                        'title',
                                        fn(Builder $query, Get $get) => $query->where('branch_id', '=', $get('branch_id'))
                                    )
                                    ->required(),
                                TextInput::make('coupon')
                                    ->belowContent(Schema::between([
                                        Action::make('validate')->action(function ($state, Get $get) {
                                            $coupon = Coupon::published()
                                                ->where('branch_id', '=', $get('branch_id'))
                                                ->where('code', '=', $state)
                                                ->first();
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
                            ]),
                    ]),
                ])
                ->action(function (array $data) {
                    $data['cart'] = collect($data['cart'])
                        ->map(function ($item) {
                            return [
                                'id' => $item['product_id'],
                                'quantity' => $item['quantity'],
                            ];
                        })
                        ->toArray();
                    try {
                        $orderService = new OrderService((new OrderPayload())->fromRequest(
                            now(),
                            $data['address_id'],
                            $data['delivery_date'],
                            $data['payment_method_id'],
                            $data['coupon'],
                            $data['cart'],
                            $data['notes'],
                            $data['branch_id'],
                            User::where('id', '=', $data['customer_id'])->first(),
                        ));
                        DB::transaction(function () use ($orderService) {
                            return $orderService
                                ->generateOrderNumber()
                                ->setOrderDate()
                                ->calculateDestination()
                                ->calculateCartPrice()
                                ->createCart()
                                // ->calculateCartWight()
                                ->calculateDeliveryPrice()
                                ->handleCouponService()
                                ->calculateFeesAndTotals()
                                ->handlePaymentMethod()
                                ->createOrder();
                        });
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
                }),
        ];
    }
}
