<?php

namespace App\Listeners;

use App\Events\RoleDeleting;

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
        $role = $event->role;
        $role->permissions()->detach();
    }
}
