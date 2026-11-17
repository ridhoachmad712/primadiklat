<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Gallery;
use App\Models\Partner;
use App\Models\Post;
use App\Models\Registration;
use App\Models\Testimonial;
use App\Models\Training;

class HomeController extends Controller
{
    public function __invoke()
    {
        $upcoming = Training::upcoming()->with('category')->take(6)->get();
        $featured = Training::upcoming()->where('is_featured', true)->with('category')->first() ?? $upcoming->first();

        return view('home', [
            'featured' => $featured,
            'upcoming' => $upcoming,
            'categories' => Category::active()->withCount(['trainings' => fn ($q) => $q->upcoming()])->get(),
            'posts' => Post::published()->take(3)->get(),
            'testimonials' => Testimonial::active()->take(6)->get(),
            'partners' => Partner::active()->get(),
            'galleries' => Gallery::where('is_published', true)->latest('event_date')->take(4)->get(),
            'stats' => [
                'alumni' => (int) setting('stat_alumni', Registration::where('status', 'attended')->count()),
                'trainings' => (int) setting('stat_trainings', Training::published()->count()),
                'partners' => (int) setting('stat_partners', Registration::distinct('institution')->count('institution')),
                'years' => (int) setting('stat_years', 1),
            ],
        ]);
    }
}
