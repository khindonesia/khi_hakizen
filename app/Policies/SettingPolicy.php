<?php

namespace App\Policies;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('setting.view');
    }

    public function view(User $user, Setting $model): bool
    {
        return $user->can('setting.view');
    }

    public function create(User $user): bool
    {
        return $user->can('setting.create');
    }

    public function update(User $user, Setting $model): bool
    {
        return $user->can('setting.update');
    }

    public function delete(User $user, Setting $model): bool
    {
        return $user->can('setting.delete');
    }
}
