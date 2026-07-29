<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = ['cliente_id'];

    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    public function subPedidos()
    {
        return $this->hasMany(PedidoEmprendimiento::class);
    }
}