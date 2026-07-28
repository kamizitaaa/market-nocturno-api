<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'emprendimiento_id', 'nombre', 'descripcion',
        'precio', 'imagen', 'disponible'
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'disponible' => 'boolean',
        ];
    }

    public function emprendimiento()
    {
        return $this->belongsTo(Emprendimiento::class);
    }

    public function carritoItems()
    {
        return $this->hasMany(CarritoItem::class);
    }
}