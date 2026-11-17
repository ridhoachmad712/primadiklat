<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasSlug;

    protected string $slugSource = 'name';

    protected $fillable = ['name', 'slug', 'description', 'sort', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function trainings(): HasMany
    {
        return $this->hasMany(Training::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort')->orderBy('name');
    }
}
