<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\ForceApproveOrderType;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Fieldset;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('force_approve_information')
                ->label('Force Approve Information')
                ->color('warning')
                ->modalSubmitAction(false)
                ->visible(function (Order $record) {
                    return $record->forceApproveOrder()->exists();
                })
                ->schema(function (Order $record) {
                    return [
                        Fieldset::make('Information')
                            ->columnSpanFull()
                            ->columns(2)
                            ->schema(function ($record) {
                                $forceApproveType = '';
                                foreach ($record->forceApproveOrder->types as $type) {
                                    $forceApproveType .= ForceApproveOrderType::from($type)->getLabel().', ';
                                }
                                $forceApproveType = substr($forceApproveType, 0, -2);
                                return [
                                    TextEntry::make('order_number')->label('Order Number')->state($record->order_number),
                                    TextEntry::make('manager_name')->label('Manager')->state($record->forceApproveOrder->user->name),
                                    TextEntry::make('created_at')->label('Created At')->state($record->forceApproveOrder->created_at),
                                    TextEntry::make('type')->label('Force Approve Type')->state($record->forceApproveOrder->created_at),
                                    TextEntry::make('reason')->label('Reason')->state($forceApproveType)->columnSpanFull(),
                                ];
                            })
                    ];
                }),
        ];
    }
}
