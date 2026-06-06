<?php

namespace App\Enums;

enum ImportType: string
{
    case Employees = 'employees';
    case Assets = 'assets';

    public function label(): string
    {
        return match ($this) {
            self::Employees => 'Employees',
            self::Assets => 'Assets',
        };
    }
}
