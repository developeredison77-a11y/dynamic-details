<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

#[Fillable(['name', 'slug', 'description', 'is_active', 'is_system'])]
class Role extends Model
{
    protected static function booted(): void
    {
        static::saving(function (Role $role): void {
            if (blank($role->slug)) {
                $role->slug = Str::slug($role->name);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_system' => 'boolean',
        ];
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->slug === 'super-admin') {
            return true;
        }

        return $this->permissions->contains('key', $permission);
    }

    /**
     * @param array<int, int|string> $permissionIds
     */
    public function syncPermissionIds(array $permissionIds): void
    {
        $this->permissions()->sync($permissionIds);
        $this->unsetRelation('permissions');
    }

    /**
     * @return Collection<int, string>
     */
    public function permissionKeys(): Collection
    {
        return $this->permissions->pluck('key');
    }
}
