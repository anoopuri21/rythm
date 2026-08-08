<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        // This will be managed from the admin settings in a later phase.
        $heroMode = 'slider';

        return view('home.index', compact('heroMode'));
    }
}
