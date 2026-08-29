<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use App\Payment\RazorpayGateway;
use App\Services\OrderService;
use App\Services\PaymentRetryService;
use App\Services\SeoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
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

        $order->load(['items.product.brand', 'payments.refunds', 'statusHistory']);

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

    public function lookupPost(Request $request): RedirectResponse
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

        return redirect(URL::temporarySignedRoute(
            'orders.show',
            now()->addMinutes(15),
            ['order' => $order],
        ));
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

    public function retryPayment(
        Order $order,
        PaymentRetryService $retries,
        OrderService $orders,
    ): RedirectResponse|View {
        abort_unless(auth()->check() && auth()->id() === $order->user_id, 403);

        try {
            $gateway = RazorpayGateway::resolve();
            $payment = $retries->reserve($order, (int) auth()->id());

            if (str_starts_with((string) $payment->gateway_order_id, 'pending_retry_')) {
                $payment = $retries->attachGatewayOrder($payment, $gateway->createOrder($order));
            }

            if (! RazorpayGateway::isConfigured()) {
                $result = $gateway->verify($order, ['status' => 'captured']);
                $orders->markPaid($order, $result, (string) $payment->gateway_order_id);

                return redirect(URL::signedRoute('checkout.success', ['order' => $order]));
            }

            return view('orders.retry-payment', [
                'order' => $order,
                'options' => [
                    'key' => (string) config('rythme.razorpay.key_id'),
                    'amount' => (int) round((float) $order->total * 100),
                    'currency' => $order->currency,
                    'name' => config('app.name'),
                    'description' => "Retry payment for {$order->order_number}",
                    'order_id' => $payment->gateway_order_id,
                    'callback_url' => route('payment.razorpay.callback'),
                    'redirect' => true,
                    'prefill' => [
                        'name' => auth()->user()->name,
                        'email' => auth()->user()->email,
                        'contact' => (string) ($order->shipping_address['phone'] ?? ''),
                    ],
                    'theme' => ['color' => '#b20202'],
                ],
            ]);
        } catch (\RuntimeException $e) {
            return back()->with('order_error', $e->getMessage());
        } catch (\Throwable) {
            return back()->with('order_error', 'The payment attempt could not be confirmed. Check this order before trying again.');
        }
    }

    /**
     * Cancel this order (owner only, pending/confirmed).
     */
    public function cancel(Request $request, Order $order, OrderService $orders): RedirectResponse
    {
        abort_unless(auth()->check() && auth()->id() === $order->user_id, 403);

        try {
            $orders->cancelByUser($order);
        } catch (\RuntimeException $e) {
            return back()->with('order_error', $e->getMessage());
        }

        return back()->with('order_success', 'Order cancelled. If payment was captured, your refund request is now pending processing.');
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
