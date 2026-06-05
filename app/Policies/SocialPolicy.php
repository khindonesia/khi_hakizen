<?php

namespace App\Policies;

use App\Models\Social;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SocialPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('social.view');
    }

    public function view(User $user, Social $model): bool
    {
        return $user->can('social.view');
    }

    public function create(User $user): bool
    {
        return $user->can('social.create');
    }

    public function update(User $user, Social $model): bool
    {
        return $user->can('social.update');
    }

    public function delete(User $user, Social $model): bool
    {
        return $user->can('social.delete');
    }
}
