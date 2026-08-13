<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class OrderController extends Controller
{
    public function __construct(private readonly SeoService $seo) {}

    /**
     * Order detail + tracking timeline — owner (or admin) only.
     */
    public function show(Request $request, Order $order): View
    {
        $this->authorizeView($request, $order);

        $order->load(['items.product.brand', 'payments', 'statusHistory']);

        $this->seo->apply([
            'meta_title' => "Order {$order->order_number} — Rhythm Exports",
            'meta_description' => 'Track your order status and view order details.',
            'robots' => 'noindex, follow',
        ]);

        return view('orders.show', [
            'order' => $order,
            'timeline' => $order->trackingTimeline(),
        ]);
    }

    /**
     * Guest lookup — order number + email match (no login needed).
     */
    public function lookup(Request $request): View
    {
        return view('orders.lookup');
    }

    public function lookupPost(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'order_number' => ['required', 'string', 'max:30'],
            'email' => ['required', 'string', 'email', 'max:254'],
        ]);

        $order = Order::query()
            ->where('order_number', $validated['order_number'])
            ->where('email', $validated['email'])
            ->first();

        if ($order === null) {
            return back()->withErrors(['order_number' => 'No order matches that number and email.'])->withInput();
        }

        return redirect()->route('orders.show', $order);
    }

    /**
     * Printable invoice — plain, print-friendly HTML from order snapshots.
     */
    public function invoice(Request $request, Order $order): View
    {
        $this->authorizeView($request, $order);

        $order->load(['items.product.brand', 'payments']);

        $this->seo->apply([
            'meta_title' => "Invoice {$order->order_number} — Rhythm Exports",
            'robots' => 'noindex, follow',
        ]);

        return view('orders.invoice', ['order' => $order]);
    }

    private function authorizeView(Request $request, Order $order): void
    {
        if (auth()->check() && auth()->id() === $order->user_id) {
            return;
        }

        // Signed guest link (email journey)
        if ($request->hasValidSignature()) {
            return;
        }

        if (auth()->guest()) {
            redirect()->guest(route('login'))->throwResponse();
        }

        abort(403);
    }
}
