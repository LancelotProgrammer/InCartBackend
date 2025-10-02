<?php

namespace App\Filament\Resources\Coupons\Schemas;

use App\Enums\CouponType;
use App\Filament\Components\TranslationComponent;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Information')
                    ->columns(2)
                    ->schema([
                        TranslationComponent::configure('title'),
                        TranslationComponent::configure('description', false),
                        TextInput::make('code')
                            ->belowContent(Schema::between([
                                Action::make('generate')->action(function (Set $set) {
                                    $set('code', Str::random(8));
                                }),
                            ]))
                            ->minLength(5)
                            ->maxLength(15)
                            ->regex('/^(?!.* {2})[\p{Arabic}a-zA-Z0-9 ]+$/u')
                            ->scopedUnique(modifyQueryUsing: function ($query, $get) {
                                return $query->where('branch_id', $get('branch_id'));
                            })
                            ->dehydrateStateUsing(fn (?string $state) => $state ? trim($state) : null)
                            ->required(),
                        Select::make('branch_id')
                            ->relationship('branch', 'title')
                            ->required(),
                        Hidden::make('type')
                            ->afterStateHydrated(function (Hidden $component) {
                                $component->state(CouponType::TIMED->value);
                            })
                            ->required(),
                        // future: add more types if needed
                        // Select::make('type')
                        //     ->columnSpanFull()
                        //     ->live()
                        //     ->options(CouponType::class)
                        //     ->required(),
                    ]),
                Section::make('Config')
                    ->columns(3)
                    ->schema(self::getCouponConfigForm()),
            ]);
    }

    private static function getCouponConfigForm()
    {
        return function (Get $get) {
            $type = (int) $get('type');

            return $type
                ? CouponType::from((int) $type)->getForm()
                : [TextEntry::make('no_coupon_has_been_selected')
                    ->columnSpanFull()
                    ->state(new HtmlString('Please select a coupon type.'))];
        };
    }
}
