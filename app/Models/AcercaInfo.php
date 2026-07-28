<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcercaInfo extends Model
{
    use HasFactory;

    protected $table = 'acerca_info';

    protected $fillable = [
        'historia_titulo', 'historia_texto', 'historia_imagen',
        'mision', 'vision'
    ];
}