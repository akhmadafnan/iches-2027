<?php

namespace App\Enums;

enum StaffDuty: string
{
    case Finance = 'finance';
    case AcademicAuthority = 'academic_authority';
    case Moderator = 'moderator';
    case CoHostPic = 'cohost_pic';

    public function requiresScope(): bool
    {
        return match ($this) {
            self::Moderator, self::CoHostPic => true,
            default => false,
        };
    }
}
