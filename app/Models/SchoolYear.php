<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SchoolYear extends Model
{
    use HasFactory;


    protected $fillable = ['name'];

    public function registration_data(): HasMany
    {
        return $this->hasMany(RegistrationData::class);
    }
}
