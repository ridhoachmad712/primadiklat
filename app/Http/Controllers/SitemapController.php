<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\Page;
use App\Models\Post;
use App\Models\Training;

class SitemapController extends Controller
{
    public function __invoke()
    {
        $urls = collect([
            ['loc' => route('home'), 'priority' => '1.0'],
            ['loc' => route('trainings.index'), 'priority' => '0.9'],
            ['loc' => route('trainings.calendar'), 'priority' => '0.7'],
            ['loc' => route('posts.index'), 'priority' => '0.7'],
            ['loc' => route('galleries.index'), 'priority' => '0.5'],
            ['loc' => route('inhouse.create'), 'priority' => '0.6'],
            ['loc' => route('contact'), 'priority' => '0.5'],
            ['loc' => route('faq'), 'priority' => '0.5'],
        ]);

        Training::published()->get(['slug', 'updated_at'])->each(fn ($t) => $urls->push(['loc' => route('trainings.show', $t), 'lastmod' => $t->updated_at, 'priority' => '0.8']));
        Post::published()->get(['slug', 'updated_at'])->each(fn ($p) => $urls->push(['loc' => route('posts.show', $p), 'lastmod' => $p->updated_at, 'priority' => '0.6']));
        Gallery::where('is_published', true)->get(['slug', 'updated_at'])->each(fn ($g) => $urls->push(['loc' => route('galleries.show', $g), 'lastmod' => $g->updated_at, 'priority' => '0.4']));
        Page::where('is_published', true)->get(['slug', 'updated_at'])->each(fn ($p) => $urls->push(['loc' => route('pages.show', $p), 'lastmod' => $p->updated_at, 'priority' => '0.4']));

        return response()->view('sitemap', ['urls' => $urls])->header('Content-Type', 'application/xml');
    }
}
