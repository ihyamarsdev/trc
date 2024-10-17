<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Devisions extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone'
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function registration_data(): HasMany
    {
        return $this->hasMany(RegistrationData::class);
    }
}
