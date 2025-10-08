<?php

namespace App\Traits;

use App\Models\Branch;
use App\Models\Category;
use App\Models\City;
use App\Models\Coupon;
use App\Models\PaymentMethod;
use App\Models\Permission;
use App\Models\Role;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;

trait HandleDeleteDependencies
{
    protected function getDeleteDependencies(): array
    {
        return [
            Branch::class => [
                'relations' => [
                    'advertisements' => 'advertisements',
                    'products' => 'branch products',
                    'users' => 'branch users',
                    'coupons' => 'coupons',
                    'orders' => 'orders',
                    'paymentMethods' => 'payment methods',
                ],
                'message' => 'This branch cannot be deleted because it has related :relations.',
            ],

            City::class => [
                'relations' => [
                    'users' => 'users',
                    'userAddresses' => 'user addresses',
                    'branches' => 'branches',
                ],
                'message' => 'This city cannot be deleted because it has related :relations.',
            ],

            Role::class => [
                'relations' => [
                    'users' => 'users',
                ],
                'message' => 'This role cannot be deleted because it has related users.',
            ],

            Coupon::class => [
                'relations' => [
                    'orders' => 'orders',
                ],
                'message' => 'This coupon cannot be deleted because it has related orders.',
            ],

            Category::class => [
                'relations' => [
                    'children' => 'subcategories',
                ],
                'message' => 'This category cannot be deleted because it has subcategories.',
            ],

            PaymentMethod::class => [
                'relations' => [
                    'orders' => 'orders',
                ],
                'message' => 'This payment method cannot be deleted because it has related orders.',
            ],

            Permission::class => [
                'relations' => [
                    'roles' => 'roles',
                ],
                'message' => 'This permission cannot be deleted because it has related roles.',
            ],
        ];
    }

    protected function deleteWithDependencyCheck(): callable
    {
        return function ($record, DeleteAction $action) {
            $dependencies = $this->getDeleteDependencies();
            $modelClass = get_class($record);

            if (isset($dependencies[$modelClass])) {
                $config = $dependencies[$modelClass];
                $foundRelations = [];

                foreach ($config['relations'] as $relation => $label) {
                    if ($record->$relation()->exists()) {
                        $foundRelations[] = $label;
                    }
                }

                if (!empty($foundRelations)) {
                    $message = str_replace(':relations', implode(', ', $foundRelations), $config['message']);

                    Notification::make()
                        ->title('Deletion Not Allowed')
                        ->body($message)
                        ->danger()
                        ->persistent()
                        ->send();

                    return false;
                }
            }

            $record->delete();

            return redirect($action->getUrl('index'));
        };
    }
}
