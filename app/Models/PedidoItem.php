<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoItem extends Model
{
    use HasFactory;

    protected $fillable = ['pedido_emprendimiento_id', 'producto_id', 'cantidad', 'precio_unitario'];

    protected function casts(): array
    {
        return ['precio_unitario' => 'decimal:2'];
    }

    public function pedidoEmprendimiento()
    {
        return $this->belongsTo(PedidoEmprendimiento::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}