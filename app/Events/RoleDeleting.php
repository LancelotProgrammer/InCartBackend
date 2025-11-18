<?php

namespace App\Events;

use App\Models\Role;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RoleDeleting
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Role $role)
    {
        Log::channel('app_log')->info('Events: Role deleting', ['role' => $this->role]);
    }
}
