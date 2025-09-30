<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-any-ticket');
    }

    public function view(User $user, Ticket $ticket): bool
    {
        return $user->hasPermission('view-ticket');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create-ticket');
    }

    public function update(User $user, Ticket $ticket): bool
    {
        return $user->hasPermission('update-ticket');
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->hasPermission('delete-ticket');
    }

    public function markImportant(User $user, Ticket $ticket): bool
    {
        return $user->hasPermission('mark-important-ticket');
    }

    public function unmarkImportant(User $user, Ticket $ticket): bool
    {
        return $user->hasPermission('unmark-important-ticket');
    }

    public function process(User $user, Ticket $ticket): bool
    {
        return $user->hasPermission('process-ticket');
    }
}