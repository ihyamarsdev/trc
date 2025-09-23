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
    public function registrationData(): HasMany
    {
        return $this->hasMany(RegistrationData::class);
    }

    /**
     * Scope a query to only include active statuses
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include statuses in a specific category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to order by the order field
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Get the next status in the flow
     */
    public function nextStatus()
    {
        return static::active()
            ->where('order', '>', $this->order)
            ->orderBy('order')
            ->first();
    }

    /**
     * Get the previous status in the flow
     */
    public function previousStatus()
    {
        return static::active()
            ->where('order', '<', $this->order)
            ->orderBy('order', 'desc')
            ->first();
    }

    /**
     * Check if this is the first status
     */
    public function isFirst()
    {
        return !static::active()
            ->where('order', '<', $this->order)
            ->exists();
    }

    /**
     * Check if this is the last status
     */
    public function isLast()
    {
        return !static::active()
            ->where('order', '>', $this->order)
            ->exists();
    }
}
