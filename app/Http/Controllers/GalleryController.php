<?php

namespace App\Http\Controllers;

use App\Models\Gallery;

class GalleryController extends Controller
{
    public function index()
    {
        return view('galleries.index', [
            'galleries' => Gallery::where('is_published', true)->latest('event_date')->paginate(12),
        ]);
    }

    public function show(Gallery $gallery)
    {
        abort_unless($gallery->is_published, 404);

        return view('galleries.show', compact('gallery'));
    }
}
