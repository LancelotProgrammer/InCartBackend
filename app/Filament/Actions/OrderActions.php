<?php

namespace App\Filament\Actions;

use App\Enums\OrderStatus;
use App\Models\Branch;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderManager;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Support\Enums\Size;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class OrderActions
{
    public static function configure(bool $hasEditAction): ActionGroup
    {
        // Edit order
        $editAction = EditAction::make();

        $actions = [

            // Cancel order
            Action::make('cancel')
                ->authorize('cancel')
                ->icon(Heroicon::XCircle)
                ->color('danger')
                ->requiresConfirmation()
                ->visible(function (Order $order) {
                    return $order->isCancelable();
                })
                ->schema([
                    Textarea::make('cancel_reason')->required(),
                ])
                ->action(function (Order $order, array $data) {
                    OrderManager::managerCancel($order, $data);
                }),

            // Approve order
            Action::make('approve')
                ->authorize('approve')
                ->icon(Heroicon::CheckCircle)
                ->color('success')
                ->requiresConfirmation()
                ->visible(function (Order $order) {
                    return $order->isApprovable();
                })
                ->action(function (Order $order) {
                    OrderManager::approve($order);
                }),

            // Assign to delivery
            Action::make('select_delivery')
                ->authorize('selectDelivery')
                ->icon(Heroicon::Truck)
                ->color('info')
                ->requiresConfirmation()
                ->visible(function (Order $order) {
                    return $order->isDeliverable();
                })
                ->schema([
                    Select::make('delivery_id')->options(function ($record) {
                        return OrderManager::getDeliveryUsers($record->branch_id);
                    }),
                ])
                ->action(function (Order $order, array $data) {
                    OrderManager::selectDelivery($order, $data);
                }),

            // Mark as finished
            Action::make('finish')
                ->authorize('finish')
                ->icon(Heroicon::DocumentCheck)
                ->color('success')
                ->requiresConfirmation()
                ->visible(function (Order $order) {
                    return $order->isFinishable();
                })
                ->action(function (Order $order) {
                    OrderManager::finish($order);
                }),

            // Archive order
            Action::make('archive')
                ->authorize('archive')
                ->icon(Heroicon::ArchiveBox)
                ->color('warning')
                ->requiresConfirmation()
                ->visible(function (Order $order) {
                    return $order->order_status === OrderStatus::FINISHED || $order->order_status === OrderStatus::CANCELLED;
                })
                ->action(function (Order $order) {
                    OrderManager::archive($order);
                }),
        ];

        if ($hasEditAction) {
            $actions[] = $editAction;
        }

        return ActionGroup::make($actions)
            ->label('More actions')
            ->icon('heroicon-m-ellipsis-vertical')
            ->size(Size::Small)
            ->color('primary')
            ->button();
    }
}
