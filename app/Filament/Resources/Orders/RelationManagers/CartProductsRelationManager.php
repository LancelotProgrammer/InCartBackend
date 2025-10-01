<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Enums\OrderStatus;
use App\Models\BranchProduct;
use App\Models\CartProduct;
use App\Models\Order;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CartProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'cartProducts';

    public function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'md' => 2,
                'xl' => 5,
            ])
            ->columns([
                Stack::make([
                    ImageColumn::make('url')->label('Image')->state(function ($record) {
                        return $record->product->files->first()->url;
                    }),
                    TextColumn::make('product.title')->searchable(),
                    TextColumn::make('quantity')->searchable(),
                    TextColumn::make('price')->searchable(),
                ]),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('edit')
                    ->icon(Heroicon::PencilSquare)
                    ->visible(function () {
                        return self::isEnabled($this->getOwnerRecord(), $this->pageClass);
                    })
                    ->fillForm(function (CartProduct $record): array {
                        return [
                            'cart_id' => $record->cart_id,
                            'product_id' => $record->product_id,
                            'quantity' => $record->quantity,
                        ];
                    })
                    ->schema(function () {
                        return [
                            Hidden::make('cart_id')
                                ->default($this->getOwnerRecord()->carts->first()->id),
                            Select::make('product_id')
                                ->relationship('product', 'title')
                                ->searchable()
                                ->getSearchResultsUsing(fn(string $search): array => Product::query()
                                    ->whereRaw('LOWER(title) LIKE ?', ['%' . strtolower($search) . '%'])
                                    ->limit(50)
                                    ->pluck('title', 'id')
                                    ->all())
                                ->getOptionLabelUsing(fn($value): ?string => Product::find($value)?->title)
                                ->required(),
                            TextInput::make('quantity')
                                ->numeric()
                                ->required(),
                        ];
                    })
                    ->action(function (array $data, CartProduct $record) {
                        $order = $this->getOwnerRecord();
                        $branchProduct = BranchProduct::where('branch_id', $order->branch_id)
                            ->where('product_id', $data['product_id'])
                            ->first();

                        $price = $branchProduct
                            ? BranchProduct::getDiscountPrice($branchProduct)
                            : 0;

                        $record->update([
                            'product_id' => $data['product_id'],
                            'quantity' => $data['quantity'],
                            'price' => $price,
                        ]);

                        $this->recalculateOrderTotals($order);
                    }),
                Action::make('delete')
                    ->color('danger')
                    ->icon(Heroicon::OutlinedTrash)
                    ->requiresConfirmation()
                    ->visible(function () {
                        return self::isEnabled($this->getOwnerRecord(), $this->pageClass);
                    })
                    ->action(function (CartProduct $record) {
                        $cart = $record->cart;
                        if ($cart->cartProducts()->count() <= 1) {
                            Notification::make()
                                ->title('Warning')
                                ->body('You cannot delete the last product in the cart.')
                                ->warning()
                                ->send();

                            return;
                        }
                        $order = $this->getOwnerRecord();
                        $record->delete();
                        $this->recalculateOrderTotals($order);
                    }),
            ])
            ->toolbarActions([
                Action::make('create')
                    ->visible(function () {
                        return self::isEnabled($this->getOwnerRecord(), $this->pageClass);
                    })
                    ->schema(function () {
                        return [
                            Hidden::make('cart_id')
                                ->default($this->getOwnerRecord()->carts->first()->id),
                            Select::make('product_id')
                                ->relationship('product', 'title')
                                ->searchable()
                                ->getSearchResultsUsing(fn(string $search): array => Product::query()
                                    ->whereRaw('LOWER(title) LIKE ?', ['%' . strtolower($search) . '%'])
                                    ->limit(50)
                                    ->pluck('title', 'id')
                                    ->all())
                                ->getOptionLabelUsing(fn($value): ?string => Product::find($value)?->title)
                                ->required(),
                            TextInput::make('quantity')
                                ->numeric()
                                ->required(),
                        ];
                    })
                    ->action(function (array $data) {
                        $order = $this->getOwnerRecord();
                        $branchProduct = BranchProduct::where('branch_id', $order->branch_id)
                            ->where('product_id', $data['product_id'])
                            ->first();

                        $price = $branchProduct
                            ? BranchProduct::getDiscountPrice($branchProduct)
                            : 0;

                        CartProduct::create([
                            'cart_id' => $data['cart_id'],
                            'product_id' => $data['product_id'],
                            'quantity' => $data['quantity'],
                            'price' => $price,
                        ]);

                        $this->recalculateOrderTotals($order);
                    }),
            ]);
    }

    public static function isEnabled(Order $order, $pageClass): bool
    {
        return $order->paymentMethod?->code === 'pay-on-delivery'
            && in_array($order->order_status, [
                OrderStatus::PENDING,
                OrderStatus::PROCESSING,
            ], true) && !(app($pageClass) instanceof ViewRecord);
    }

    protected function recalculateOrderTotals(Order $order): void
    {
        $subtotal = $order->cartProducts->sum(
            fn($cartProduct) => $cartProduct->price * $cartProduct->quantity
        );

        $order->update([
            'subtotal_price' => $subtotal,
            'total_price' => $subtotal - $order->coupon_discount
                + $order->delivery_fee
                + $order->service_fee
                + $order->tax_amount,
        ]);
    }
}
