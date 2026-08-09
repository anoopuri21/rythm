<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        // Hero mode is config-driven (RYTHME_HERO_MODE env): 'slider' | 'video'
        $heroMode = config('rythme.hero_mode', 'slider');

        return view('home.index', compact('heroMode'));
    }
}
