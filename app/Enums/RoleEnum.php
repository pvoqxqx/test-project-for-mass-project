<?php

namespace App\Enums;

enum RoleEnum: int
{
    case ADMIN = 1;
    case MODERATOR = 2;
    case USER = 3;

    public static function labels(): array
    {
        return [
            self::ADMIN->value => 1,
            self::MODERATOR->value => 2,
            self::USER->value => 3,
        ];
    }

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 1,
            self::MODERATOR => 2,
            self::USER => 3,
        };
    }
}
