<?php

namespace App\Filament\Actions;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\OrderService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Support\Enums\Size;
use Filament\Support\Icons\Heroicon;

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
                    OrderService::managerCancel($order, $data);
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
                    OrderService::managerApprove($order);
                }),

            // Force approve order
            Action::make('force_approve')
                ->authorize('forceApprove')
                ->icon(Heroicon::CheckBadge)
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Force Approve order')
                ->modalDescription('Are you sure you\'d like to force approve this order? This means you are approving an order which it\'s date is not today or it\'s not checked out.')
                ->modalSubmitActionLabel('Yes, Approve it')
                ->visible(function (Order $order) {
                    return $order->isForceApprovable();
                })
                ->action(function (Order $order) {
                    OrderService::managerForceApprove($order);
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
                        return OrderService::getDeliveryUsers($record->branch_id);
                    }),
                ])
                ->action(function (Order $order, array $data) {
                    OrderService::managerSelectDelivery($order, $data);
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
                    OrderService::managerFinish($order);
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
                    OrderService::managerArchive($order);
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
