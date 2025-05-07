<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\Bid;
use App\Models\User;

class BidPolicy
{
    public function create(User $authUser): bool
    {
        return in_array($authUser->role_id, [RoleEnum::ADMIN->value, RoleEnum::USER->value]);
    }

    public function update(User $authUser): bool
    {
        return in_array($authUser->role_id, [RoleEnum::ADMIN->value, RoleEnum::MODERATOR->value]);
    }

    public function delete(User $authUser): bool
    {
        return $authUser->role_id === RoleEnum::ADMIN->value;
    }

    public function show(User $authUser, Bid $bid): bool
    {
        return in_array($authUser->role_id, [RoleEnum::ADMIN->value, RoleEnum::MODERATOR->value]) || $authUser->email === $bid->email;
    }

    public function index(User $authUser): bool
    {
        return in_array($authUser->role_id, [RoleEnum::ADMIN->value, RoleEnum::MODERATOR->value]);
    }
}
