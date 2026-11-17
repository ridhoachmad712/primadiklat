<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Gallery extends Model
{
    use HasSlug;

    protected $fillable = ['training_id', 'title', 'slug', 'description', 'event_date', 'images', 'is_published'];

    protected $casts = [
        'event_date' => 'date',
        'images' => 'array',
        'is_published' => 'boolean',
    ];

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function getImageUrlsAttribute(): array
    {
        return collect($this->images ?? [])->map(fn ($p) => Storage::disk('public')->url($p))->all();
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->image_urls[0] ?? null;
    }
}
