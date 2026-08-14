<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

final class CartController extends Controller
{
    public function index(): View
    {
        return view('cart.index');
    }
}
