<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasSlug;

    protected $fillable = ['title', 'slug', 'content', 'is_published', 'meta_description'];

    protected $casts = ['is_published' => 'boolean'];
}
