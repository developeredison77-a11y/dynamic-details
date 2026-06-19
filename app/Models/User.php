<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'profile_image', 'role_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function canAccess(string $permission): bool
    {
        if (! $this->relationLoaded('role')) {
            $this->load('role.permissions');
        }

        $role = $this->role;

        if ($role?->is_active === true && $role->hasPermission($permission)) {
            return true;
        }

        return $this->role_id === null && $this->isBootstrapAdmin();
    }

    private function isBootstrapAdmin(): bool
    {
        if ($this->email === env('ADMIN_EMAIL', 'admin@example.com')) {
            return true;
        }

        return static::query()->orderBy('id')->value('id') === $this->id;
    }
}
