<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Toko extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_toko';

    protected $fillable = [
        'nama',
        'slug',
        'status',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'id_toko', 'id_toko');
    }
}
