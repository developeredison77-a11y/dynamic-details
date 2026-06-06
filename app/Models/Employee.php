<?php

namespace App\Models;

use App\Enums\EmployeeStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'employee_code',
    'name_en',
    'name_ar',
    'department',
    'designation',
    'email',
    'phone',
    'status',
    'joined_at',
    'status_changed_at',
    'notes',
])]
class Employee extends Model
{
    protected function casts(): array
    {
        return [
            'status' => EmployeeStatus::class,
            'joined_at' => 'date',
            'status_changed_at' => 'date',
        ];
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class);
    }

    public function activeAssignments(): HasMany
    {
        return $this->assignments()->assigned();
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, function (Builder $query, string $term): void {
            $query->where(function (Builder $query) use ($term): void {
                $query->where('employee_code', 'like', "%{$term}%")
                    ->orWhere('name_en', 'like', "%{$term}%")
                    ->orWhere('name_ar', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('department', 'like', "%{$term}%");
            });
        });
    }
}
