<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Page;
use App\Models\Post;
use App\Models\Training;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function show(Page $page)
    {
        abort_unless($page->is_published, 404);

        return view('pages.show', compact('page'));
    }

    public function faq()
    {
        return view('pages.faq', ['faqs' => Faq::active()->get()]);
    }

    public function search(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $trainings = collect();
        $posts = collect();

        if (mb_strlen($q) >= 2) {
            $trainings = Training::published()->with('category')
                ->where(fn ($w) => $w->where('title', 'like', "%{$q}%")->orWhere('excerpt', 'like', "%{$q}%")->orWhere('city', 'like', "%{$q}%"))
                ->orderByDesc('start_date')->take(12)->get();
            $posts = Post::published()
                ->where(fn ($w) => $w->where('title', 'like', "%{$q}%")->orWhere('excerpt', 'like', "%{$q}%"))
                ->take(6)->get();
        }

        return view('pages.search', compact('q', 'trainings', 'posts'));
    }
}
