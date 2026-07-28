<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MfaCode extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'codigo', 'expira_en', 'usado'];

    protected function casts(): array
    {
        return [
            'expira_en' => 'datetime',
            'usado' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}