<?php

namespace App\Http\Controllers;

use App\Models\Closure;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $closures = Closure::query()
            ->with('hawkerCenter')
            ->upcoming(30)
            ->orderBy('start_date')
            ->get();

        return view('home', [
            'closures' => $closures,
        ]);
    }
}
