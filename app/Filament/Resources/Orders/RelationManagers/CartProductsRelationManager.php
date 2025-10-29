<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Models\BranchProduct;
use App\Models\CartProduct;
use App\Models\Product;
use App\Policies\OrderPolicy;
use App\Services\OrderService;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CartProductsRelationManager extends RelationManager
{
    protected static bool $isLazy = false;

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
                    ImageColumn::make('url')->label('Image')->state(fn($record) => $record?->product->files->first()->url ?? null)->placeholder('Deleted Product')->imageSize(200),
                    TextColumn::make('title')->searchable()->prefix('Title: '),
                    TextColumn::make('quantity')->searchable()->prefix('Quantity: '),
                    TextColumn::make('price')->searchable()->prefix('Price: '),
                    TextColumn::make('Total')->searchable()->state(fn($record) => $record->price * $record->quantity)->prefix('Total: '),
                ]),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('edit')
                    ->icon(Heroicon::PencilSquare)
                    ->visible(function () {
                        return OrderPolicy::isEnabled($this->getOwnerRecord());
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
                            Hidden::make('cart_id')->default($this->getOwnerRecord()->carts->first()->id),
                            Select::make('product_id')->relationship('product', 'title')->disabled()->dehydrated(),
                            TextInput::make('quantity')
                                ->required()
                                ->integer()
                                ->minValue(function (Get $get) {
                                    $branchProduct = BranchProduct::published()->where('branch_id', '=', $this->getOwnerRecord()->branch_id)
                                        ->where('product_id', '=', $get('product_id'))
                                        ->first();

                                    if ($branchProduct) {
                                        return $branchProduct->minimum_order_quantity;
                                    } else {
                                        return 0;
                                    }
                                })
                                ->maxValue(function (Get $get) {
                                    $branchProduct = BranchProduct::published()->where('branch_id', '=', $this->getOwnerRecord()->branch_id)
                                        ->where('product_id', '=', $get('product_id'))
                                        ->first();

                                    if ($branchProduct) {
                                        return $branchProduct->maximum;
                                    } else {
                                        return 0;
                                    }
                                }),
                        ];
                    })
                    ->action(function (array $data, CartProduct $record) {
                        OrderService::editProduct($data, $record, $this->getOwnerRecord());
                    }),
                Action::make('delete')
                    ->color('danger')
                    ->icon(Heroicon::OutlinedTrash)
                    ->requiresConfirmation()
                    ->visible(function () {
                        return OrderPolicy::isEnabled($this->getOwnerRecord());
                    })
                    ->action(function (CartProduct $record) {
                        OrderService::removeProduct($record, $this->getOwnerRecord());
                    }),
            ])
            ->toolbarActions([
                Action::make('create')
                    ->visible(function () {
                        return OrderPolicy::isEnabled($this->getOwnerRecord());
                    })
                    ->schema(function () {
                        return [
                            Hidden::make('cart_id')
                                ->default($this->getOwnerRecord()->carts->first()->id),
                            Select::make('product_id')
                                ->required()
                                ->searchable()
                                ->getSearchResultsUsing(fn(string $search): array => Product::query()
                                    ->whereRaw('LOWER(title) LIKE ?', ['%' . strtolower($search) . '%'])
                                    ->limit(50)
                                    ->pluck('title', 'id')
                                    ->all())
                                ->getOptionLabelUsing(fn($value): ?string => Product::find($value)?->title)
                                ->distinct()
                                ->live(),
                            TextInput::make('quantity')
                                ->required()
                                ->integer()
                                ->minValue(function (Get $get) {
                                    $branchProduct = BranchProduct::published()->where('branch_id', '=', $this->getOwnerRecord()->branch_id)
                                        ->where('product_id', '=', $get('product_id'))
                                        ->first();

                                    if ($branchProduct) {
                                        return $branchProduct->minimum_order_quantity;
                                    } else {
                                        return 0;
                                    }
                                })
                                ->maxValue(function (Get $get) {
                                    $branchProduct = BranchProduct::published()->where('branch_id', '=', $this->getOwnerRecord()->branch_id)
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
                    ->action(function (array $data) {
                        OrderService::addProduct($data, $this->getOwnerRecord());
                    }),
            ]);
    }
}
