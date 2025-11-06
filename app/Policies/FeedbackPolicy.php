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
        return false;
    }

    public function update(User $user, Feedback $feedback): bool
    {
        return false;
    }

    public function delete(User $user, Feedback $feedback): bool
    {
        return $user->hasPermission('delete-feedback') && $user->belongsToUserBranch($feedback);
    }

    public function markImportant(User $user, Feedback $feedback): bool
    {
        return $user->hasPermission('mark-important-feedback') && $user->belongsToUserBranch($feedback);
    }

    public function unmarkImportant(User $user, Feedback $feedback): bool
    {
        return $user->hasPermission('unmark-important-feedback') && $user->belongsToUserBranch($feedback);
    }

    public function process(User $user, Feedback $feedback): bool
    {
        return $user->hasPermission('process-feedback') && $user->belongsToUserBranch($feedback);
    }

    public function changeBranch(User $user, Feedback $feedback): bool
    {
        return $user->hasPermission('change-branch-feedback') && $user->belongsToUserBranch($feedback);
    }
}
