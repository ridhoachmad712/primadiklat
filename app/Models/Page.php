<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasSlug;

    protected $fillable = ['title', 'slug', 'content', 'is_published', 'meta_title', 'meta_description', 'noindex'];

    protected string $publicRoute = 'pages.show';

    protected $casts = ['is_published' => 'boolean', 'noindex' => 'boolean'];
}
