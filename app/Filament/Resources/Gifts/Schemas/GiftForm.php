<?php

namespace App\Filament\Resources\Gifts\Schemas;

use App\Filament\Components\TranslationComponent;
use App\Models\Coupon;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class GiftForm
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
                    ]),
                Section::make('Config')
                    ->columns(4)
                    ->schema([
                        TextInput::make('code')
                            ->belowContent(Schema::between([
                                Action::make('generate')->action(function (Set $set) {
                                    $set('code', Str::random(8));
                                }),
                            ]))
                            ->minLength(5)
                            ->maxLength(15)
                            ->regex('/^(?!.* {2})[\p{Arabic}a-zA-Z0-9 ]+$/u')
                            ->dehydrateStateUsing(fn(?string $state) => $state ? trim($state) : null)
                            ->required()
                            ->rules([
                                fn(): Closure => function (string $attribute, $value, Closure $fail) {
                                    if (Coupon::where('code', $value)->exists()) {
                                        $fail("The code is used in coupons");
                                    }
                                },
                            ]),
                        TextInput::make('points')->required()->numeric()->rule('min:0'),
                        TextInput::make('discount')->required()->numeric()->rule('min:0'),
                        TextInput::make('allowed_sub_total_price')->required()->numeric()->rule('min:0'),
                    ]),
            ]);
    }
}
