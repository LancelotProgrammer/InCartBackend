<?php

namespace App\Listeners;

use App\Events\RoleDeleting;
use Illuminate\Support\Facades\Log;

class DeleteRolePermissions
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(RoleDeleting $event): void
    {
        Log::info('Listeners: delete role permissions', ['role' => $event->role]);

        $role = $event->role;
        $role->permissions()->detach();
    }
}
