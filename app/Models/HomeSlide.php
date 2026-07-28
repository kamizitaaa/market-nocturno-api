<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeSlide extends Model
{
    use HasFactory;

    protected $table = 'home_slides';

    protected $fillable = ['imagen', 'titulo', 'orden', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }
}