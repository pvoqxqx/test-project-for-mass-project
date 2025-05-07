<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\User;

class UserPolicy
{
    public function update(User $authUser, User $user)
    {
        return $authUser->id === $user->id || $authUser->role_id === RoleEnum::ADMIN->value;
    }

    public function delete(User $authUser): bool
    {
        return $authUser->role_id === RoleEnum::ADMIN->value;
    }

    public function show(User $authUser): bool
    {
        return $authUser->role_id === RoleEnum::ADMIN->value;
    }
}
