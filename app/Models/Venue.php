<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Kota + hotel pilihan tempat kegiatan. */
class Venue extends Model
{
    protected $fillable = ['city', 'name', 'address', 'maps_url', 'is_active', 'sort_order'];

    protected $attributes = ['is_active' => true, 'sort_order' => 0];

    protected $casts = ['is_active' => 'boolean'];

    public function trainings(): HasMany
    {
        return $this->hasMany(Training::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('city')->orderBy('name');
    }

    public function getLabelAttribute(): string
    {
        return $this->city.' · '.$this->name;
    }
}
