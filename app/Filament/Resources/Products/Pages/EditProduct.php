<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Traits\HasConcurrentEditingProtection;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    use HasConcurrentEditingProtection;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getConcurrencyAttributes(): array
    {
        return [
            $this->record?->updated_at?->toDateTimeString(),
            $this->record?->categories()?->count(),
            $this->record?->branchProducts()?->max('branch_product.updated_at'),
        ];
    }
}
