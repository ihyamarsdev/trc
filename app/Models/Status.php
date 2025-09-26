<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Status extends Model
{
    protected $fillable = [
        'name',
        'description',
        'color',
        'order',
        'category',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get all registration data with this status
     */
    public function registration_data(): HasMany
    {
        return $this->hasMany(RegistrationData::class);
    }
}
