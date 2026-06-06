<?php

namespace App\Models;

use App\Enums\AssetCondition;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['asset_assignment_id', 'asset_id', 'employee_id', 'received_by', 'returned_at', 'condition', 'notes'])]
class AssetReturn extends Model
{
    protected function casts(): array
    {
        return [
            'returned_at' => 'date',
            'condition' => AssetCondition::class,
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(AssetAssignment::class, 'asset_assignment_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
