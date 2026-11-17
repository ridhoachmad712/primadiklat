<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Partner;
use App\Models\Post;
use App\Models\Registration;
use App\Models\ScheduleDate;
use App\Models\Testimonial;
use App\Models\Topic;
use App\Models\Training;
use App\Models\Venue;

class HomeController extends Controller
{
    public function __invoke()
    {
        $openDates = ScheduleDate::open()->get();
        $year = $openDates->first()?->start_date->year ?? now()->year;
        $yearDates = ScheduleDate::active()->whereYear('start_date', $year)->get();
        $topics = Topic::active()->with('category')->get();
        $venues = Venue::active()->get();

        return view('home', [
            'year' => $year,
            'openDates' => $openDates,
            'yearDates' => $yearDates,
            'yearMonths' => $yearDates->groupBy(fn ($d) => $d->start_date->month),
            'topics' => $topics,
            'featuredTopics' => $topics->sortByDesc('is_featured')->take(6),
            'venues' => $venues,
            'cities' => $venues->groupBy('city'),
            'categories' => Category::active()->withCount(['topics' => fn ($q) => $q->where('is_active', true)])->get()->where('topics_count', '>', 0),
            'posts' => Post::published()->take(3)->get(),
            'testimonials' => Testimonial::active()->take(6)->get(),
            'partners' => Partner::active()->get(),
            'stats' => [
                'alumni' => (int) setting('stat_alumni', Registration::where('status', 'attended')->count()),
                'trainings' => (int) setting('stat_trainings', Training::published()->count()),
                'partners' => (int) setting('stat_partners', Registration::distinct('institution')->count('institution')),
                'years' => (int) setting('stat_years', 1),
            ],
        ]);
    }
}
