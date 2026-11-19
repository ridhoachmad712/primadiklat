<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Partner;
use App\Models\Post;
use App\Models\ScheduleDate;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\Topic;
use App\Models\Venue;

class HomeController extends Controller
{
    public function __invoke()
    {
        $openDates = ScheduleDate::open()->get();
        $year = $openDates->first()?->start_date->year ?? now()->year;
        $topics = Topic::active()->with('category')->get();
        $cities = Venue::active()->get()->groupBy('city');
        $config = collect(Setting::homeSections())->where('enabled', true)->keyBy('key');
        $limit = fn (string $key, int $fallback) => $config->get($key)['limit'] ?? $fallback;

        $data = [
            'year' => $year,
            'openDates' => $openDates,
            'yearCount' => ScheduleDate::active()->whereYear('start_date', $year)->count(),
            'topics' => $topics,
            // Featured first, then admin sort order.
            'featuredTopics' => $topics->values()->sortBy(fn ($t, $i) => [$t->is_featured ? 0 : 1, $i])->take($limit('topics', 6))->values(),
            'cities' => $cities,
            'categories' => Category::active()->withCount(['topics' => fn ($q) => $q->where('is_active', true)])->get()->where('topics_count', '>', 0),
            'posts' => $config->has('posts') ? Post::published()->take($limit('posts', 3))->get() : collect(),
            'testimonials' => $config->has('testimonials') ? Testimonial::active()->take($limit('testimonials', 3))->get() : collect(),
            'partners' => Partner::active()->get(),
            'legal' => Setting::legalItems(),
            'contacts' => Setting::contacts(),
            'stats' => array_filter([
                'Alumni peserta' => filled(setting('stat_alumni')) ? number_format((int) setting('stat_alumni'), 0, ',', '.').'+' : null,
                'Tahun pengalaman' => filled(setting('stat_years')) ? (int) setting('stat_years').'+' : null,
                'Kota pelaksanaan' => $cities->count() ?: null,
            ]),
        ];

        // Sections with nothing to show are skipped so the page never renders an empty block.
        $data['sections'] = $config->filter(fn ($s) => match ($s['key']) {
            'testimonials' => $data['testimonials']->isNotEmpty(),
            'posts' => $data['posts']->isNotEmpty(),
            'trust' => $data['legal'] || $data['stats'] || $data['partners']->isNotEmpty(),
            default => true,
        })->values();

        return view('home', $data);
    }
}
