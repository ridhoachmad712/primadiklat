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
        $isLive = $post->is_published && (! $post->published_at || $post->published_at->isPast());
        if (! $isLive) {
            // Draft preview: editors/super admins, or the writer who owns it. Everyone else gets 404.
            $user = auth()->user();
            abort_unless($user && ($user->hasRole(\App\Models\User::ROLE_EDITOR) || $post->user_id === $user->id), 404);
        } else {
            $post->increment('views');
        }

        return view('posts.show', [
            'post' => $post,
            'preview' => ! $isLive,
            'latest' => Post::published()->whereKeyNot($post->id)->take(4)->get(),
        ]);
    }
}
