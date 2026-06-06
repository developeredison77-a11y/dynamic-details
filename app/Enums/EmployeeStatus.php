<?php

namespace App\Enums;

enum EmployeeStatus: string
{
    case Active = 'active';
    case Leave = 'leave';
    case Resigned = 'resigned';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Leave => 'Leave',
            self::Resigned => 'Resigned',
        };
    }
}
