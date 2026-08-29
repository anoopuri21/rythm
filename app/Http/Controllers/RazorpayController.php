<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Payment\RazorpayGateway;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PaymentEventService;
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
                // An untrusted or incomplete browser callback must not mutate
                // payment state. A verified webhook or a later valid callback
                // can still reconcile this initiated payment safely.
                Log::warning('Razorpay callback verification failed', [
                    'order_id' => $order->id,
                    'message' => $result->message,
                ]);

                return redirect()->route('checkout.index')->with('cart-error', 'We could not verify your payment. Please try again or check your order status.');
            }

            $orders->markPaid($order, $result, (string) $payment->gateway_order_id);
            $cart->clear();

            return redirect(URL::signedRoute('checkout.success', ['order' => $order]));
        } catch (\Throwable $e) {
            Log::error('Razorpay callback error', ['exception' => $e::class]);

            return redirect()->route('checkout.index')->with('cart-error', 'Something went wrong processing your payment.');
        }
    }

    public function webhook(Request $request, OrderService $orders, PaymentEventService $events): JsonResponse
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
            $eventType = (string) ($payload['event'] ?? '');
            $acceptedEvents = ['payment.authorized', 'payment.captured', 'order.paid'];
            $entity = $payload['payload']['payment']['entity'] ?? [];
            $gatewayOrderId = is_array($entity) ? (string) ($entity['order_id'] ?? '') : '';
            $payment = in_array($eventType, $acceptedEvents, true)
                ? Payment::query()
                    ->where('gateway', 'razorpay')
                    ->where('gateway_order_id', $gatewayOrderId)
                    ->latest()
                    ->first()
                : null;
            $receipt = $events->receive(
                $rawBody,
                $payload,
                $payment,
                $request->header('X-Razorpay-Event-Id'),
            );
            $event = $receipt['event'];

            if (! $receipt['payload_matches']) {
                Log::warning('Razorpay webhook: event identity reused with a different payload');

                return response()->json(['error' => 'Event identity conflict'], 409);
            }

            if (! $receipt['is_new']) {
                return match ($event->status) {
                    PaymentEvent::STATUS_PROCESSED => response()->json(['status' => 'ok', 'replayed' => true]),
                    PaymentEvent::STATUS_FAILED => response()->json(['status' => 'accepted', 'replayed' => true]),
                    default => response()->json(['status' => 'processing'], 202),
                };
            }

            if (! in_array($eventType, $acceptedEvents, true)) {
                $events->processed($event);

                return response()->json(['status' => 'ignored']);
            }

            if ($payment === null || $payment->order === null) {
                $events->failed($event, 'Gateway order not found.');
                Log::warning('Razorpay webhook: order not found');

                return response()->json(['status' => 'accepted']);
            }

            $result = $eventType === 'payment.authorized'
                ? $events->verifyAuthorizedPayment($payment, $payload)
                : $events->verifyCapturedPayment($payment, $payload);
            if (! $result->success) {
                $events->failed($event, $result->message ?? 'Webhook rejected.');

                return response()->json(['status' => 'accepted']);
            }

            if ($eventType === 'payment.authorized') {
                $orders->markPaymentAuthorized($payment->order, $result, $gatewayOrderId);
            } else {
                $orders->markPaid($payment->order, $result, $gatewayOrderId);
            }
            $events->processed($event);

            return response()->json(['status' => 'ok']);
        } catch (\JsonException) {
            return response()->json(['error' => 'Invalid JSON'], 400);
        } catch (\Throwable $e) {
            Log::error('Razorpay webhook error', ['exception' => $e::class]);

            return response()->json(['error' => 'Internal error'], 500);
        }
    }
}
