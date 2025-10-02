<?php

namespace App\Policies;

use App\Models\Feedback;
use App\Models\User;

class FeedbackPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-any-feedback');
    }

    public function view(User $user, Feedback $feedback): bool
    {
        return $user->hasPermission('view-feedback');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create-feedback');
    }

    public function update(User $user, Feedback $feedback): bool
    {
        return $user->hasPermission('update-feedback');
    }

    public function delete(User $user, Feedback $feedback): bool
    {
        return $user->hasPermission('delete-feedback');
    }

    public function markImportant(User $user, Feedback $feedback): bool
    {
        return $user->hasPermission('mark-important-feedback');
    }

    public function unmarkImportant(User $user, Feedback $feedback): bool
    {
        return $user->hasPermission('unmark-important-feedback');
    }

    public function process(User $user, Feedback $feedback): bool
    {
        return $user->hasPermission('process-feedback');
    }
}
