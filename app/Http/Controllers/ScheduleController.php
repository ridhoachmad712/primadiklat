<?php

namespace App\Http\Controllers;

use App\Models\ScheduleDate;
use App\Models\Topic;
use App\Models\Venue;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $years = ScheduleDate::active()->get(['start_date'])->map(fn ($d) => $d->start_date->year)->unique()->sort()->values();
        $default = $years->first(fn ($y) => $y >= now()->year) ?? $years->last() ?? now()->year;
        $year = (int) $request->query('tahun', $default);

        $dates = ScheduleDate::active()->whereYear('start_date', $year)->get();

        return view('schedule.index', [
            'year' => $year,
            'years' => $years,
            'months' => $dates->groupBy(fn ($d) => $d->start_date->month),
            'dates' => $dates,
            'next' => ScheduleDate::open()->first(),
            'venues' => Venue::active()->get()->groupBy('city'),
            'topicCount' => Topic::active()->count(),
        ]);
    }
}
