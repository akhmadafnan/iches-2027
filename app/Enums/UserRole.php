<?php

namespace App\Enums;

enum UserRole: string
{
    case User = 'user';
    case Reviewer = 'reviewer';
    case Committee = 'committee';
    case Admin = 'admin';
    case Superadmin = 'superadmin';

    public function label(): string
    {
        return match ($this) {
            self::User => 'Participant',
            self::Reviewer => 'Reviewer',
            self::Committee => 'Committee',
            self::Admin => 'Administrator',
            self::Superadmin => 'Superadmin',
        };
    }
}
