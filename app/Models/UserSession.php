<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'ip_address', 'navegador',
        'fecha_inicio', 'activa'
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'datetime',
            'activa' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}