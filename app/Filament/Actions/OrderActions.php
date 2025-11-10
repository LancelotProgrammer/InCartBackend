<?php

namespace App\Filament\Actions;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
                ->schema([
                    Textarea::make('reason')->required(),
                    TextInput::make('password')->currentPassword()->password()->required(),
                ])
                ->visible(function (Order $order) {
                    return $order->isForceApprovable();
                })
                ->action(function (Order $order, array $data) {
                    OrderService::managerForceApprove($order, $data);
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
                    })->required(),
                ])
                ->action(function (Order $order, array $data) {
                    OrderService::managerSelectDelivery($order, $data);
                    redirect(OrderResource::getUrl('index'));
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

            // Mark as closed
            Action::make('close')
                ->authorize('close')
                ->icon(Heroicon::Flag)
                ->color('primary')
                ->requiresConfirmation()
                ->visible(fn (Order $order) => $order->isClosable())
                ->schema(function (Order $order) {
                    return $order->isPayOnDelivery()
                        ? [
                            TextInput::make('payed_price')
                                ->numeric()
                                ->required(),
                        ]
                        : [];
                })
                ->action(function (Order $order, array $data) {
                    OrderService::managerClose($order, $data);
                }),

            // Archive order
            Action::make('archive')
                ->authorize('archive')
                ->icon(Heroicon::ArchiveBox)
                ->color('warning')
                ->requiresConfirmation()
                ->visible(function (Order $order) {
                    return $order->isArchivable();
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
            ->icon(Heroicon::EllipsisVertical)
            ->size(Size::Small)
            ->color('primary')
            ->button();
    }
}
