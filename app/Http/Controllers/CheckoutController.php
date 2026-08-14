<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class CheckoutController extends Controller
{
    public function index(): View
    {
        return view('checkout.index');
    }

    public function success(Request $request, Order $order): View
    {
        abort_unless($request->hasValidSignature(), 403);
        abort_unless($order->user_id === auth()->id(), 403);

        $order->load(['items.product.brand', 'payments']);

        return view('checkout.success', ['order' => $order]);
    }
}
