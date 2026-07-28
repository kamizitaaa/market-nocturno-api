<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'email',
        'telefono',
        'password',
        'role',
        'mfa_enabled',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'mfa_enabled' => 'boolean',
        ];
    }

    // ===== RELACIONES =====
    public function emprendimientos()
    {
        return $this->hasMany(Emprendimiento::class);
    }

    public function carritos()
    {
        return $this->hasMany(Carrito::class, 'cliente_id');
    }

    public function mfaCodes()
    {
        return $this->hasMany(MfaCode::class);
    }

    public function sessions()
    {
        return $this->hasMany(UserSession::class);
    }

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class);
    }

    // ===== HELPERS DE ROL =====
    public function isSuperadmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isEmprendedor(): bool
    {
        return $this->role === 'emprendedor';
    }

    public function isCliente(): bool
    {
        return $this->role === 'cliente';
    }
}