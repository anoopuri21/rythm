<?php

declare(strict_types=1);

namespace App\Livewire;

use App\DTOs\CheckoutData;
use App\Payment\RazorpayGateway;
use App\Services\AddressService;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\SiteSettingsService;
use App\Services\OrderService;
use Illuminate\Support\Facades\URL;
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

    public function selectAddress(int $addressId): void
    {
        $this->addressId = $addressId;
        $this->error = null;
        $this->step = 2;
    }

    public function saveNewAddress(AddressService $addresses, CartService $cart): void
    {
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
        $this->couponError = null;

        try {
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
    public function placeOrder(OrderService $orders, CartService $cart, AddressService $addresses, SiteSettingsService $settings): void
    {
        $this->placing = true;
        $this->paymentError = null;

        try {
            $user = auth()->user();

            if ($user === null) {
                throw new RuntimeException('Please sign in to continue.');
            }

            $cartModel = $cart->getOrCreateCart();
            $totals = $cart->totals();

            if ($totals['count'] === 0) {
                throw new RuntimeException('Your cart is empty.');
            }

            $address = $addresses->forUser($user->id)->firstWhere('id', $this->addressId);

            if ($address === null) {
                throw new RuntimeException('Please choose a delivery address.');
            }

            $data = new CheckoutData(
                addressId: $address->id,
                shippingAddress: $addresses->snapshot($address),
                billingAddress: $addresses->snapshot($address),
                subtotal: $totals['subtotal'],
                discount: $this->couponDiscount,
                shippingFee: $this->shippingFeeFor($totals['subtotal'], $settings),
                tax: $this->taxFor($totals['subtotal'] - $this->couponDiscount, $settings),
                currency: 'INR',
                couponCode: $this->appliedCoupon,
            );

            $order = $orders->createFromCheckout($cartModel, $data, $user->id);

            $gateway = RazorpayGateway::isConfigured()
                ? RazorpayGateway::fromConfig()
                : app(\App\Payment\FakePaymentGateway::class);

            $gatewayOrderId = $gateway->createOrder($order);
            $orders->recordPaymentInitiation($order, $gatewayOrderId);

            $this->orderId = $order->id;
            $this->gatewayOrderId = $gatewayOrderId;

            // Fake gateway (no keys configured) — simulate immediate success.
            if (! RazorpayGateway::isConfigured()) {
                $this->confirmPayment(['status' => 'captured'], $orders, $cart);
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
            if ($this->orderId === null) {
                throw new RuntimeException('No pending order found.');
            }

            $order = \App\Models\Order::with('items.product')->findOrFail($this->orderId);

            $gateway = RazorpayGateway::isConfigured()
                ? RazorpayGateway::fromConfig()
                : app(\App\Payment\FakePaymentGateway::class);

            $result = $gateway->verify($order, $payload);

            if (! $result->success) {
                $orders->markFailed($order, $result);
                $this->paymentError = $result->message ?? 'Payment failed. Please try again.';

                return;
            }

            $orders->markPaid($order, $result, $this->gatewayOrderId);

            if ($this->appliedCoupon !== null) {
                $coupon = \App\Models\Coupon::where('code', $this->appliedCoupon)->first();
                if ($coupon !== null) {
                    app(CouponService::class)->incrementUsage($coupon);
                }
            }

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
}
