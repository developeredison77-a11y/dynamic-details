<?php

namespace App\Enums;

enum AssetAssignmentStatus: string
{
    case Assigned = 'assigned';
    case Returned = 'returned';

    public function label(): string
    {
        return match ($this) {
            self::Assigned => 'Assigned',
            self::Returned => 'Returned',
        };
    }
}
