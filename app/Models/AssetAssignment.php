<?php

namespace App\Models;

use App\Enums\AssetAssignmentStatus;
use App\Enums\AssetCondition;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'employee_id',
    'asset_id',
    'created_by',
    'status',
    'handover_date',
    'expected_return_date',
    'returned_at',
    'handover_notes',
    'return_notes',
    'return_condition',
])]
class AssetAssignment extends Model
{
    protected function casts(): array
    {
        return [
            'status' => AssetAssignmentStatus::class,
            'handover_date' => 'date',
            'expected_return_date' => 'date',
            'returned_at' => 'date',
            'return_condition' => AssetCondition::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function returnRecord(): HasOne
    {
        return $this->hasOne(AssetReturn::class);
    }

    public function declaration(): HasOne
    {
        return $this->hasOne(AssetDeclaration::class);
    }

    public function scopeAssigned(Builder $query): Builder
    {
        return $query->where('asset_assignments.status', AssetAssignmentStatus::Assigned);
    }

    public function canBeEdited(): bool
    {
        return $this->status === AssetAssignmentStatus::Assigned
            && $this->handover_date !== null
            && $this->handover_date->isAfter(now(config('app.timezone', 'Asia/Kolkata'))->startOfDay());
    }
}
