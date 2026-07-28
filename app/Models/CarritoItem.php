<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarritoItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'carrito_id', 'producto_id', 'cantidad',
        'estado', 'actualizado_en'
    ];

    protected function casts(): array
    {
        return ['actualizado_en' => 'datetime'];
    }

    public function carrito()
    {
        return $this->belongsTo(Carrito::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}