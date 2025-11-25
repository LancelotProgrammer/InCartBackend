<?php

namespace App\Filament\Resources\Branches\Pages;

use App\Filament\Resources\Branches\BranchResource;
use App\Models\Branch;
use App\Models\PaymentMethod;
use App\Traits\HandleDeleteDependencies;
use App\Traits\HasConcurrentEditingProtection;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

class EditBranch extends EditRecord
{
    use HandleDeleteDependencies, HasConcurrentEditingProtection;

    protected static string $resource = BranchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->using(fn($record, $action) => (new static)->deleteWithDependencyCheck()($record, $action)),
            Action::make('setup_payments_methods')
                ->requiresConfirmation()
                ->before(function (Action $action, Branch $record) {
                    if ($record->paymentMethods->count() > 0) {
                        Notification::make()
                            ->warning()
                            ->title('Branch payment methods setup failed')
                            ->body('Some payment methods already exist for this branch. please delete them first')
                            ->persistent()
                            ->send();
                        $action->halt();
                    }
                })
                ->action(function ($record) {
                    PaymentMethod::insert([
                        [
                            'branch_id' => $record->id,
                            'code' => PaymentMethod::PAY_ON_DELIVERY_CODE,
                            'title' => json_encode(Factory::translations(['en', 'ar'], ['Pay on Delivery', 'الدفع عند الاستلام']), JSON_UNESCAPED_UNICODE),
                            'published_at' => now(),
                            'order' => '1',
                        ],
                        [
                            'branch_id' => $record->id,
                            'code' => 'apple-pay',
                            'title' => json_encode(Factory::translations(['en', 'ar'], ['Apple Pay', 'Apple Pay']), JSON_UNESCAPED_UNICODE),
                            'published_at' => null,
                            'order' => '2',
                        ],
                        [
                            'branch_id' => $record->id,
                            'code' => 'google-pay',
                            'title' => json_encode(Factory::translations(['en', 'ar'], ['Google Pay', 'Google Pay']), JSON_UNESCAPED_UNICODE),
                            'published_at' => null,
                            'order' => '2',
                        ],
                        [
                            'branch_id' => $record->id,
                            'code' => 'mada-pay',
                            'title' => json_encode(Factory::translations(['en', 'ar'], ['Mada Pay', 'Mada Pay']), JSON_UNESCAPED_UNICODE),
                            'published_at' => null,
                            'order' => '3',
                        ],
                        [
                            'branch_id' => $record->id,
                            'code' => 'stc-pay',
                            'title' => json_encode(Factory::translations(['en', 'ar'], ['STC Pay', 'STC Pay']), JSON_UNESCAPED_UNICODE),
                            'published_at' => null,
                            'order' => '3',
                        ],
                    ]);

                    Notification::make()
                        ->warning()
                        ->title('Branch payment methods setup successfully')
                        ->send();
                })
        ];
    }
}
