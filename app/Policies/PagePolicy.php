<?php

namespace App\Policies;

use App\Models\Page;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('page.view');
    }

    public function view(User $user, Page $model): bool
    {
        return $user->can('page.view');
    }

    public function create(User $user): bool
    {
        return $user->can('page.create');
    }

    public function update(User $user, Page $model): bool
    {
        return $user->can('page.update');
    }

    public function delete(User $user, Page $model): bool
    {
        return $user->can('page.delete');
    }
}
