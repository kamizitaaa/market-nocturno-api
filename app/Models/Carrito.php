<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
    use HasFactory;

    protected $fillable = ['cliente_id', 'estado'];

    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    public function items()
    {
        return $this->hasMany(CarritoItem::class);
    }
}