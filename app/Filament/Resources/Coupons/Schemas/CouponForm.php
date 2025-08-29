<?php

namespace App\Filament\Resources\Coupons\Schemas;

use App\Enums\CouponType;
use App\Filament\Components\TranslationComponent;
use App\Rules\TimedCouponType;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
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
                        TranslationComponent::configure('description'),
                        TextInput::make('code')
                            ->belowContent(Schema::between([
                                Action::make('generate')->action(function (Set $set) {
                                    $set('code', Str::random(8));
                                }),
                            ]))
                            ->rules([
                                'min:5',
                                'max:15',
                                'regex:/^(?!.* {2})[\p{Arabic}a-zA-Z0-9 ]+$/u',
                            ])
                            ->dehydrateStateUsing(fn(?string $state) => $state ? trim($state) : null)
                            ->password()
                            ->revealable()
                            ->required(),
                        Select::make('branch_id')
                            ->relationship('branch', 'title')
                            ->required(),
                        Hidden::make('type')
                            ->afterStateHydrated(function (Hidden $component) {
                                $component->state(CouponType::TIMED->value);
                            })
                            ->required(),
                    ]),
                Section::make('Config')
                    ->columns(3)
                    ->schema([
                        KeyValue::make('config')
                            ->columnSpan(2)
                            ->addable(false)
                            ->deletable(false)
                            ->editableKeys(false)
                            ->rules([new TimedCouponType()])
                            ->afterStateHydrated(function (KeyValue $component) {
                                $component->state('{"value":"","start_date":"","end_date":"","use_limit":"","user_limit":""}');
                            })
                            ->required(),
                        TextEntry::make('config_description')
                            ->state(new HtmlString('
                                <ul style="margin:0; padding-left:18px; line-height:1.7;">
                                    <li><strong>value</strong> -> <span>قيمة مبلغ المالي للعرض .</span></li>
                                    <li><strong>start_date</strong> -> <span>تاريخ بداية صلاحية العرض أو الكوبون.</span></li>
                                    <li><strong>end_date</strong> -> <span>تاريخ انتهاء صلاحية العرض أو الكوبون.</span></li>
                                    <li><strong>use_limit</strong> -> <span>عدد المرات المسموح باستخدام العرض بشكل عام.</span></li>
                                    <li><strong>user_limit</strong> -> <span>عدد المرات المسموح لكل مستخدم فردي أن يستفيد من العرض.</span></li>
                                </ul>
                            ')),
                    ]),
            ]);
    }
}
