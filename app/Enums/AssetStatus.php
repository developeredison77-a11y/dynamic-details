<?php

namespace App\Enums;

enum AssetStatus: string
{
    case Available = 'available';
    case Assigned = 'assigned';
    case Returned = 'returned';
    case Maintenance = 'maintenance';
    case Retired = 'retired';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Available',
            self::Assigned => 'Assigned',
            self::Returned => 'Returned',
            self::Maintenance => 'Maintenance',
            self::Retired => 'Retired',
        };
    }
}
