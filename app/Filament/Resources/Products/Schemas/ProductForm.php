<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\UnitType;
use App\Filament\Components\TranslationComponent;
use App\Models\Branch;
use App\Models\Category;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components(function (?Model $record) {

                $entryArray = [];
                $counter = 1;
                if ($record !== null) {
                    foreach ($record->files as $file) {
                        $entryArray[] = ImageEntry::make('advertisement.file.number.'.$counter)->label('file '.$counter)->state($file->url);
                        $counter++;
                    }
                }

                $components = [
                    Section::make('information')
                        ->columns(2)
                        ->schema([
                            TranslationComponent::configure('title')
                                ->required(),
                            TranslationComponent::configure('description'),
                            TextInput::make('brand'),
                            TextInput::make('sku'),
                            Select::make('unit')->options(UnitType::class),
                            Select::make('category_id')
                                ->multiple()
                                ->relationship('categories', 'title')
                                ->searchable()
                                ->getSearchResultsUsing(fn(string $search): array => Category::query()
                                    ->whereRaw('LOWER(title) LIKE ?', ['%' . strtolower($search) . '%'])
                                    ->whereNotNull('parent_id')
                                    ->limit(50)
                                    ->pluck('title', 'id')
                                    ->all())
                                ->getOptionLabelUsing(fn($value): ?string => Category::find($value)?->title)
                                ->required(),
                        ]),
                    Section::make('configs')->schema([
                        Repeater::make('branches')
                            ->label('Branches Configs')
                            ->columns(4)
                            ->relationship('branchProducts')
                            ->schema([
                                Select::make('branch_id')->disabled()->relationship('branch', 'title'),
                                TextInput::make('price'),
                                TextInput::make('discount'),
                                TextInput::make('maximum_order_quantity')->numeric(),
                                TextInput::make('minimum_order_quantity')->numeric(),
                                TextInput::make('quantity')->numeric(),
                                DatePicker::make('expires_at'),
                                Toggle::make('published_at')
                                    ->visible(function () {
                                        return auth()->user()->canPublishProduct();
                                    })
                                    ->dehydrateStateUsing(
                                        function ($state) {
                                            return $state ? Carbon::now() : null;
                                        }
                                    )
                                    ->inline(false),
                            ])
                            ->defaultItems(function () {
                                return Branch::count();
                            })
                            ->afterStateHydrated(function (Repeater $component, ?Model $record) {
                                if ($record === null) {
                                    $items = [];
                                    foreach (Branch::all() as $index => $branch) {
                                        $items['item'.($index + 1)] = [
                                            'branch_id' => $branch->id,
                                        ];
                                    }
                                    $component->state($items);
                                }
                            })
                            ->reorderable(false)
                            ->deletable(false)
                            ->addable(false)
                            ->required(),
                    ]),
                    Section::make('images')
                        ->columns(function () use ($counter) {
                            return $counter - 1 <= 4 ? $counter - 1 : 5;
                        })
                        ->schema([
                            ...$record !== null ? $entryArray : [FileUpload::make('files')->image()->multiple()->disk('public')->directory('products')->visibility('public')],
                        ]),
                ];

                return $components;
            });
    }
}
