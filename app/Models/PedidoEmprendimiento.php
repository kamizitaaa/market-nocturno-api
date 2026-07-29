<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoEmprendimiento extends Model
{
    use HasFactory;

    protected $fillable = ['pedido_id', 'emprendimiento_id', 'estado'];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function emprendimiento()
    {
        return $this->belongsTo(Emprendimiento::class);
    }

    public function items()
    {
        return $this->hasMany(PedidoItem::class);
    }
}