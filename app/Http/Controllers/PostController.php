<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->query('kategori');

        return view('posts.index', [
            'posts' => Post::published()
                ->when($category && array_key_exists($category, Post::CATEGORIES), fn ($q) => $q->where('category', $category))
                ->paginate(9)
                ->withQueryString(),
            'category' => $category,
        ]);
    }

    public function show(Post $post)
    {
        abort_unless(($post->is_published && (! $post->published_at || $post->published_at->isPast())) || auth()->check(), 404);
        $post->increment('views');

        return view('posts.show', [
            'post' => $post,
            'latest' => Post::published()->whereKeyNot($post->id)->take(4)->get(),
        ]);
    }
}
