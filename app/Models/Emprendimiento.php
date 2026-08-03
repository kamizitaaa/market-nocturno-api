<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emprendimiento extends Model
{
    use HasFactory;

    protected $fillable = [
    'user_id', 'categoria_id', 'nombre', 'precio_desde',
    'precio_hasta', 'estado', 'destacado', 'descripcion', 'imagen', 'fecha'
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'precio_desde' => 'decimal:2',
            'precio_hasta' => 'decimal:2',
            'destacado' => 'boolean',
        ];
    }

    public function emprendedor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function productos()
    {
        return $this->hasMany(Producto::class);
    }
}