<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLES = ['admin', 'client', 'freelance'];

    protected $fillable = [
        'name', 'email', 'password', 'role', 'title', 'bio', 'skills', 'hourly_rate',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'client_id');
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class, 'freelancer_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    public function isFreelance(): bool
    {
        return $this->role === 'freelance';
    }

    public function roleLabel(): string
    {
        return ['admin' => 'Administrateur', 'client' => 'Client', 'freelance' => 'Freelance'][$this->role] ?? $this->role;
    }

    public function skillList(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $this->skills))));
    }
}
