<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ScheduleDate;
use App\Models\Topic;
use App\Models\Venue;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $category = $request->query('kategori');

        $topics = Topic::active()->with('category')
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w->where('title', 'like', "%{$q}%")->orWhere('excerpt', 'like', "%{$q}%")))
            ->when($category, fn ($query, $slug) => $query->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->get();

        return view('topics.index', [
            'topics' => $topics,
            'q' => $q,
            'category' => $category,
            'categories' => Category::active()->withCount(['topics' => fn ($query) => $query->where('is_active', true)])->get()->where('topics_count', '>', 0),
        ]);
    }

    public function show(Topic $topic)
    {
        abort_unless($topic->is_active, 404);

        return view('topics.show', [
            'topic' => $topic->load('category'),
            'dates' => ScheduleDate::open()->take(12)->get(),
            'venues' => Venue::active()->get(),
            'related' => Topic::active()->whereKeyNot($topic->id)
                ->when($topic->category_id, fn ($query) => $query->where('category_id', $topic->category_id))
                ->take(3)->get(),
        ]);
    }
}
