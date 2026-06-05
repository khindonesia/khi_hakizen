<?php

namespace App\Policies;

use App\Models\User;
use App\Models\KnowledgeBase;

class KnowledgeBasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('knowledge.view');
    }

    public function view(User $user, KnowledgeBase $model): bool
    {
        return $user->can('knowledge.view');
    }

    public function create(User $user): bool
    {
        return $user->can('knowledge.create');
    }

    public function update(User $user, KnowledgeBase $model): bool
    {
        return $user->can('knowledge.update');
    }

    public function delete(User $user, KnowledgeBase $model): bool
    {
        return $user->can('knowledge.delete');
    }
}
