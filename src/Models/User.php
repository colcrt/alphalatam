<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Model;

class User extends Model
{
    protected string $table = 'users';

    protected array $fillable = [
        'name',
        'email',
        'avatar_path',
    ];

    protected array $hidden = [
        'password',
        'remember_token',
    ];

    protected array $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected bool $timestamps = false;

    public function esAdmin(): bool
    {
        return $this->attributes['role'] === 'admin';
    }

    public function puedePublicar(): bool
    {
        return in_array($this->attributes['role'], ['admin', 'editor'], true);
    }

    public function esSoloLectura(): bool
    {
        return in_array($this->attributes['role'], ['revisor', 'auditor'], true);
    }
}
