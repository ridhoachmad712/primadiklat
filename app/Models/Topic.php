<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Tema/materi bimtek in the catalog; can be held on any schedule date at any venue. */
class Topic extends Model
{
    use HasSlug;

    protected $fillable = [
        'category_id', 'type', 'title', 'slug', 'excerpt', 'description', 'materials', 'target_participants',
        'thumbnail', 'is_active', 'is_featured', 'sort_order',
    ];

    protected $attributes = ['type' => 'bimtek', 'is_active' => true, 'is_featured' => false, 'sort_order' => 0];

    protected $casts = ['is_active' => 'boolean', 'is_featured' => 'boolean'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function trainings(): HasMany
    {
        return $this->hasMany(Training::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('title');
    }

    public function getTypeLabelAttribute(): string
    {
        return Training::TYPES[$this->type] ?? ucfirst($this->type);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return storage_url($this->thumbnail);
    }
}
