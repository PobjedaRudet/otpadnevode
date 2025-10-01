<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    use HasFactory;

    public $timestamps = false; // table has no created_at / updated_at

    protected $fillable = [
        'firma',
        'mjrni_instrument',
        'vrijeme',
        'datum',
        'vrijednost',
    ];

    protected $casts = [
        'vrijeme' => 'datetime',
        'datum' => 'date',
        'vrijednost' => 'decimal:2',
    ];
}
