<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use Illuminate\View\View;

class CollectionDirectoryController extends Controller
{
    public function index(): View
    {
        $collections = Collection::public()
            ->with('user')
            ->withCount('items')
            ->latest()
            ->paginate(24);

        return view('colecoes.directory', [
            'collections' => $collections,
        ]);
    }
}
