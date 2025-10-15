<?php

namespace App\Enums;

use App\ExternalServices\FirebaseFCM;
use App\Models\Product;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Contracts\HasLabel;

enum FirebaseFCMLinks: string implements HasLabel
{
    case PRODUCT = 'product';

    public function getLabel(): string
    {
        return match ($this) {
            self::PRODUCT => 'Product',
        };
    }

    public static function getLinksModelsForm(): array
    {
        return [
            Select::make('product_id')
                ->columnSpanFull()
                ->visible(function (Get $get) {
                    $link = $get('link');

                    return isset($link) ? $get('link')->value === 'product' : null;
                })
                ->relationship('product', 'title')
                ->searchable()
                ->getSearchResultsUsing(fn (string $search): array => Product::query()
                    ->whereRaw('LOWER(title) LIKE ?', ['%'.strtolower($search).'%'])
                    ->limit(50)
                    ->pluck('title', 'id')
                    ->all())
                ->getOptionLabelUsing(fn ($value): ?string => Product::find($value)?->title),
        ];
    }

    public static function getModelDeepLink(array $data): ?string
    {
        return isset($data['link']) ? match ($data['link']->value) {
            self::PRODUCT->value => isset($data['product_id']) ? FirebaseFCM::PRODUCT_DEEP_LINK.'/'.$data['product_id'] : null,
            default => null,
        } : null;
    }
}
