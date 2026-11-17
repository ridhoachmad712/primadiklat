<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Training;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TrainingController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['q', 'kategori', 'jenis', 'kota', 'bulan', 'metode', 'status']);

        $query = Training::published()
            ->with('category')
            ->withCount(['registrations' => fn ($q) => $q->where('status', '!=', 'cancelled')])
            ->when($filters['q'] ?? null, fn ($q, $v) => $q->where(fn ($w) => $w->where('title', 'like', "%{$v}%")->orWhere('excerpt', 'like', "%{$v}%")))
            ->when($filters['kategori'] ?? null, fn ($q, $v) => $q->whereHas('category', fn ($c) => $c->where('slug', $v)))
            ->when($filters['jenis'] ?? null, fn ($q, $v) => $q->where('type', $v))
            ->when($filters['kota'] ?? null, fn ($q, $v) => $q->where('city', $v))
            ->when($filters['metode'] ?? null, fn ($q, $v) => $q->where('method', $v))
            ->when($filters['bulan'] ?? null, function ($q, $v) {
                try {
                    $m = Carbon::createFromFormat('Y-m', $v)->startOfMonth();
                    $q->whereBetween('start_date', [$m, $m->copy()->endOfMonth()]);
                } catch (\Throwable) {
                }
            });

        if (($filters['status'] ?? null) === 'selesai') {
            $query->whereDate('end_date', '<', today())->orderByDesc('start_date');
        } else {
            $query->whereDate('end_date', '>=', today())->orderBy('start_date');
        }

        return view('trainings.index', [
            'trainings' => $query->paginate(12)->withQueryString(),
            'filters' => $filters,
            'categories' => Category::active()->get(),
            'cities' => Training::published()->whereNotNull('city')->distinct()->orderBy('city')->pluck('city'),
            'months' => collect(range(0, 11))->map(fn ($i) => now()->startOfMonth()->addMonths($i)),
        ]);
    }

    public function calendar(Request $request)
    {
        try {
            $month = Carbon::createFromFormat('Y-m', (string) $request->query('bulan', now()->format('Y-m')))->startOfMonth();
        } catch (\Throwable) {
            $month = now()->startOfMonth();
        }

        $gridStart = $month->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $trainings = Training::published()
            ->with('category')
            ->whereDate('start_date', '<=', $gridEnd)
            ->whereDate('end_date', '>=', $gridStart)
            ->orderBy('start_date')
            ->get();

        return view('trainings.calendar', compact('month', 'gridStart', 'gridEnd', 'trainings'));
    }

    public function show(Training $training)
    {
        abort_unless($training->status === 'published' || auth()->check(), 404);

        $training->increment('views');
        $training->loadCount(['registrations' => fn ($q) => $q->where('status', '!=', 'cancelled')]);

        return view('trainings.show', [
            'training' => $training->load('category', 'galleries'),
            'related' => Training::upcoming()
                ->whereKeyNot($training->id)
                ->when($training->category_id, fn ($q) => $q->orderByRaw('category_id = ? desc', [$training->category_id]))
                ->with('category')
                ->take(3)
                ->get(),
        ]);
    }

    public function invitation(Training $training)
    {
        abort_unless($training->status === 'published' || auth()->check(), 404);

        $pdf = Pdf::loadView('pdf.invitation', [
            'training' => $training->load('category'),
            'number' => sprintf('%03d/PD-UND/%s/%s', $training->id, $this->roman($training->start_date->month), $training->start_date->year),
        ])->setPaper('a4');

        return $pdf->stream('Undangan-'.$training->slug.'.pdf');
    }

    private function roman(int $n): string
    {
        return ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'][$n];
    }
}
