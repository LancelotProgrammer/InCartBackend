<?php

namespace App\Listeners;

use App\Events\RoleDeleting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

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
