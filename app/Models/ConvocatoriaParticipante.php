<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConvocatoriaParticipante extends Model
{
    use HasFactory;

    protected $fillable = [
        'convocatoria_id', 'nombre', 'telefono', 'email', 'tipo_negocio', 'mensaje'
    ];

    public function convocatoria()
    {
        return $this->belongsTo(Convocatoria::class);
    }
}