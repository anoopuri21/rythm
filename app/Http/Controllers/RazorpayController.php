<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Payment\RazorpayGateway;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

/**
 * Razorpay callback (browser redirect / web) + async webhook.
 * Both paths cryptographically verify before touching order state.
 */
final class RazorpayController extends Controller
{
    public function callback(Request $request, OrderService $orders, CartService $cart): RedirectResponse
    {
        $payload = $request->only(['razorpay_payment_id', 'razorpay_order_id', 'razorpay_signature']);

        $payment = Payment::query()
            ->where('gateway_order_id', $payload['razorpay_order_id'] ?? '')
            ->latest()
            ->first();

        if ($payment === null) {
            Log::warning('Razorpay callback: unknown gateway order id');

            return redirect()->route('checkout.index')->with('cart-error', 'We could not match your payment. Please try again.');
        }

        $order = $payment->order;

        try {
            $gateway = RazorpayGateway::fromConfig();
            $result = $gateway->verify($order, $payload);

            if (! $result->success) {
                $orders->markFailed($order, $result);

                return redirect()->route('checkout.index')->with('cart-error', $result->message ?? 'Payment failed.');
            }

            $orders->markPaid($order, $result, (string) $payment->gateway_order_id);
            $cart->clear();

            return redirect(URL::signedRoute('checkout.success', ['order' => $order]));
        } catch (\Throwable $e) {
            Log::error('Razorpay callback error', ['error' => $e->getMessage()]);

            return redirect()->route('checkout.index')->with('cart-error', 'Something went wrong processing your payment.');
        }
    }

    public function webhook(Request $request, OrderService $orders): JsonResponse
    {
        $rawBody = $request->getContent();
        $signature = (string) $request->header('X-Razorpay-Signature', '');

        try {
            $gateway = RazorpayGateway::fromConfig();

            if (! $gateway->verifyWebhookSignature($rawBody, $signature)) {
                Log::warning('Razorpay webhook: invalid signature');

                return response()->json(['error' => 'Invalid signature'], 400);
            }

            $payload = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
            $result = $gateway->handleWebhook($payload);

            if (! $result->success) {
                return response()->json(['error' => $result->message ?? 'Webhook rejected'], 422);
            }

            // Locate the order via the gateway order id recorded at initiation.
            $entity = $payload['payload']['payment']['entity']
                ?? $payload['payload']['order']['entity']
                ?? null;

            $gatewayOrderId = (string) ($entity['order_id'] ?? $entity['id'] ?? '');

            $payment = Payment::query()
                ->where('gateway_order_id', $gatewayOrderId)
                ->latest()
                ->first();

            if ($payment === null) {
                Log::warning('Razorpay webhook: order not found', ['gateway_order_id' => $gatewayOrderId]);

                return response()->json(['error' => 'Order not found'], 404);
            }

            if (! $payment->order->isPaid()) {
                $orders->markPaid($payment->order, $result, $gatewayOrderId);
            }

            return response()->json(['status' => 'ok']);
        } catch (\Throwable $e) {
            Log::error('Razorpay webhook error', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'Internal error'], 500);
        }
    }
}
