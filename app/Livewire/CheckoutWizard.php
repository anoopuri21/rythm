<?php

declare(strict_types=1);

namespace App\Livewire;

use App\DTOs\CheckoutData;
use App\Models\Order;
use App\Models\Payment;
use App\Payment\RazorpayGateway;
use App\Services\AddressService;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\OrderService;
use App\Services\SiteSettingsService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;
use RuntimeException;

final class CheckoutWizard extends Component
{
    public int $step = 1;

    public ?int $addressId = null;

    public bool $showNewAddress = false;

    // New-address form fields
    public string $name = '';

    public string $phone = '';

    public ?string $email = null;

    public string $line1 = '';

    public ?string $line2 = null;

    public string $city = '';

    public string $state = '';

    public string $pincode = '';

    public bool $isDefault = false;

    public ?string $error = null;

    public ?string $paymentError = null;

    public ?string $couponCode = null;

    public ?string $couponError = null;

    public float $couponDiscount = 0.0;

    public ?string $appliedCoupon = null;

    public ?int $orderId = null;

    public ?string $gatewayOrderId = null;

    public bool $placing = false;

    public bool $confirming = false;

    public string $checkoutToken = '';

    public function mount(): void
    {
        $this->checkoutToken = (string) Str::uuid();
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'regex:/^[0-9+\-\s]{10,15}$/'],
            'email' => ['nullable', 'email', 'max:254'],
            'line1' => ['required', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'pincode' => ['required', 'string', 'regex:/^[0-9]{6}$/'],
        ];
    }

    public function selectAddress(int $addressId, AddressService $addresses): void
    {
        abort_unless(auth()->check(), 403);

        if (! $addresses->forUser((int) auth()->id())->contains('id', $addressId)) {
            $this->addressId = null;
            $this->error = 'Please choose a valid delivery address.';
            $this->step = 1;

            return;
        }

        $this->addressId = $addressId;
        $this->error = null;
        $this->step = 2;
    }

    public function saveNewAddress(AddressService $addresses, CartService $cart): void
    {
        abort_unless(auth()->check(), 403);
        $this->validate();

        $data = [
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email ?: auth()->user()?->email,
            'line1' => $this->line1,
            'line2' => $this->line2,
            'city' => $this->city,
            'state' => $this->state,
            'pincode' => $this->pincode,
            'is_default' => $this->isDefault,
        ];

        $address = $addresses->store((int) auth()->id(), $data);
        $this->addressId = $address->id;
        $this->showNewAddress = false;
        $this->resetFormFields();
        $this->step = 2;
    }

    public function applyCoupon(CouponService $coupons): void
    {
        abort_unless(auth()->check(), 403);
        $this->couponError = null;

        try {
            $this->guardRateLimit('coupon', 20, 60);
            $totals = app(CartService::class)->totals();
            $result = $coupons->validateAndApply((string) $this->couponCode, $totals['subtotal']);

            $this->appliedCoupon = $result['coupon']->code;
            $this->couponDiscount = $result['discount'];
            $this->couponCode = null;
        } catch (RuntimeException $e) {
            $this->couponError = $e->getMessage();
            $this->appliedCoupon = null;
            $this->couponDiscount = 0.0;
        }
    }

    public function removeCoupon(): void
    {
        $this->appliedCoupon = null;
        $this->couponDiscount = 0.0;
        $this->couponError = null;
    }

    public function backToAddresses(): void
    {
        $this->step = 1;
        $this->paymentError = null;
    }

    /**
     * Create the order + payment initiation, then return gateway info.
     */
    public function placeOrder(OrderService $orders, CartService $cart, AddressService $addresses): void
    {
        $this->placing = true;
        $this->paymentError = null;

        try {
            $user = auth()->user();

            if ($user === null) {
                throw new RuntimeException('Please sign in to continue.');
            }

            $this->guardRateLimit('place-order', 5, 60);
            $cartModel = $cart->getOrCreateCart();
            $totals = $cart->totals();

            if ($totals['count'] === 0) {
                throw new RuntimeException('Your cart is empty.');
            }

            $address = $addresses->forUser($user->id)->firstWhere('id', $this->addressId);

            if ($address === null) {
                throw new RuntimeException('Please choose a delivery address.');
            }

            // Fail closed before creating/reserving an order when no approved
            // payment gateway is available for this environment.
            $gateway = RazorpayGateway::resolve();

            $data = new CheckoutData(
                addressId: $address->id,
                shippingAddress: $addresses->snapshot($address),
                billingAddress: $addresses->snapshot($address),
                currency: 'INR',
                couponCode: $this->appliedCoupon,
                idempotencyKey: $this->checkoutToken,
            );

            $order = $orders->createFromCheckout($cartModel, $data, $user->id);

            $this->orderId = $order->id;

            if ($order->isPaid()) {
                $this->redirect(URL::signedRoute('checkout.success', ['order' => $order]));

                return;
            }

            $payment = $order->payments()
                ->where('status', Payment::STATUS_INITIATED)
                ->latest()
                ->first();
            $gatewayOrderId = $payment?->gateway_order_id ?? $gateway->createOrder($order);

            if ($payment === null) {
                $orders->recordPaymentInitiation($order, $gatewayOrderId);
            }

            $this->gatewayOrderId = $gatewayOrderId;

            // Fake gateway (no keys configured) — simulate immediate success.
            if (! RazorpayGateway::isConfigured()) {
                $this->confirmPayment(['status' => 'captured'], $orders, $cart);
            } else {
                $this->dispatch('razorpay-open', options: [
                    'key' => (string) config('services.razorpay.key_id'),
                    'amount' => (int) round((float) $order->total * 100),
                    'currency' => $order->currency,
                    'name' => config('app.name'),
                    'description' => "Order {$order->order_number}",
                    'order_id' => $gatewayOrderId,
                    'callback_url' => route('payment.razorpay.callback'),
                    'redirect' => true,
                    'prefill' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'contact' => (string) ($data->shippingAddress['phone'] ?? ''),
                    ],
                    'theme' => ['color' => '#b20202'],
                ]);
            }
        } catch (RuntimeException $e) {
            $this->paymentError = $e->getMessage();
        } finally {
            $this->placing = false;
        }
    }

    /**
     * Verify + finalize the payment (called by Razorpay JS on success,
     * or automatically by the fake gateway).
     *
     * @param  array<string, mixed>  $payload
     */
    public function confirmPayment(array $payload, OrderService $orders, CartService $cart): void
    {
        $this->confirming = true;

        try {
            $this->guardRateLimit('confirm-payment', 10, 60);
            if ($this->orderId === null) {
                throw new RuntimeException('No pending order found.');
            }

            $order = Order::query()
                ->whereKey($this->orderId)
                ->where('user_id', auth()->id())
                ->with('items.product')
                ->first();

            if ($order === null) {
                throw new RuntimeException('No pending order found.');
            }

            $gateway = RazorpayGateway::resolve();

            $result = $gateway->verify($order, $payload);

            if (! $result->success) {
                $orders->markFailed($order, $result);
                $this->paymentError = $result->message ?? 'Payment failed. Please try again.';

                return;
            }

            $orders->markPaid($order, $result, $this->gatewayOrderId);

            $cart->clear();

            $this->dispatch('cart-updated');

            $this->redirect(URL::signedRoute('checkout.success', ['order' => $order]));
        } catch (RuntimeException $e) {
            $this->paymentError = $e->getMessage();
        } finally {
            $this->confirming = false;
        }
    }

    public function render(AddressService $addresses, CartService $cart, SiteSettingsService $settings): View
    {
        $cartItems = $cart->items();
        $totals = $cart->totals();

        if ($totals['count'] === 0 && $this->orderId === null) {
            $this->step = 1;
        }

        $discounted = max(0.0, $totals['subtotal'] - $this->couponDiscount);
        $shippingFee = $this->shippingFeeFor($totals['subtotal'], $settings);
        $tax = $this->taxFor($discounted, $settings);

        return view('livewire.checkout-wizard', [
            'addresses' => $addresses->forUser(auth()->id()),
            'cartItems' => $cartItems,
            'totals' => $totals,
            'shippingFee' => $shippingFee,
            'tax' => $tax,
            'grandTotal' => round($discounted + $shippingFee + $tax, 2),
            'razorpayConfigured' => RazorpayGateway::isConfigured(),
        ]);
    }

    private function shippingFeeFor(float $subtotal, SiteSettingsService $settings): float
    {
        $flat = $settings->getFloat('shipping_flat_fee', 0.0);
        $freeAbove = $settings->getFloat('shipping_free_above', 0.0);

        if ($freeAbove > 0 && $subtotal >= $freeAbove) {
            return 0.0;
        }

        return $flat;
    }

    private function taxFor(float $discountedSubtotal, SiteSettingsService $settings): float
    {
        $rate = $settings->getFloat('tax_rate', 0.0);

        return round($discountedSubtotal * ($rate / 100), 2);
    }

    private function resetFormFields(): void
    {
        $this->reset('name', 'phone', 'email', 'line1', 'line2', 'city', 'state', 'pincode', 'isDefault');
    }

    private function guardRateLimit(string $action, int $attempts, int $decaySeconds): void
    {
        $identity = auth()->id() !== null ? 'user:'.auth()->id() : 'ip:'.request()->ip();
        $key = "checkout:{$action}:{$identity}";

        if (RateLimiter::tooManyAttempts($key, $attempts)) {
            throw new RuntimeException('Too many attempts. Please wait a moment and try again.');
        }

        RateLimiter::hit($key, $decaySeconds);
    }
}
