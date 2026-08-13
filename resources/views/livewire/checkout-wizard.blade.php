<div class="mx-auto max-w-7xl px-5 py-10 sm:px-8 sm:py-14 lg:px-12">
    <nav aria-label="Breadcrumb" class="mb-8 flex items-center gap-2 text-xs text-muted">
        <a href="{{ route('home') }}" class="transition hover:text-brand">Home</a>
        <span aria-hidden="true" class="text-ink/30">/</span>
        <a href="{{ route('cart.index') }}" class="transition hover:text-brand">Cart</a>
        <span aria-hidden="true" class="text-ink/30">/</span>
        <span class="font-semibold text-ink" aria-current="page">Checkout</span>
    </nav>

    <p class="section-kicker mb-4">Secure checkout</p>
    <h1 class="section-title">Almost there.</h1>

    {{-- Steps indicator --}}
    <ol class="mt-8 flex items-center gap-3 text-xs font-bold sm:gap-4" aria-label="Checkout progress">
        <li class="flex items-center gap-2 {{ $step >= 1 ? 'text-brand' : 'text-muted' }}">
            <span class="flex h-7 w-7 items-center justify-center rounded-full {{ $step >= 1 ? 'bg-brand text-white' : 'bg-ink/10 text-muted' }}">1</span>
            Address
        </li>
        <li class="h-px w-8 bg-ink/15 sm:w-16" aria-hidden="true"></li>
        <li class="flex items-center gap-2 {{ $step >= 2 ? 'text-brand' : 'text-muted' }}">
            <span class="flex h-7 w-7 items-center justify-center rounded-full {{ $step >= 2 ? 'bg-brand text-white' : 'bg-ink/10 text-muted' }}">2</span>
            Payment
        </li>
    </ol>

    <div class="mt-10 grid gap-8 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)] lg:gap-12">
        <div>
            {{-- ===== STEP 1 · ADDRESS ===== --}}
            @if($step === 1)
                <section aria-label="Delivery address">
                    @if(session('cart-error'))
                        <p class="mb-5 rounded-xl bg-brand/10 px-4 py-3 text-sm font-semibold text-brand" role="alert">{{ session('cart-error') }}</p>
                    @endif

                    {{-- Saved addresses --}}
                    @if($addresses->isNotEmpty())
                        <div class="grid gap-3 sm:grid-cols-2" wire:key="saved-addresses">
                            @foreach($addresses as $address)
                                <button type="button" wire:click="selectAddress({{ $address->id }})"
                                        class="rounded-2xl border p-5 text-left transition
                                        {{ $addressId === $address->id ? 'border-brand bg-brand/5 ring-2 ring-brand/20' : 'border-ink/10 bg-white hover:border-brand/40' }}">
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="font-bold text-ink">{{ $address->name }}
                                            @if($address->is_default)
                                                <span class="ml-1.5 rounded-full bg-brand/10 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-brand">Default</span>
                                            @endif
                                        </p>
                                    </div>
                                    <p class="mt-2 text-sm leading-6 text-muted">
                                        {{ $address->line1 }}{{ $address->line2 ? ', ' . $address->line2 : '' }}<br>
                                        {{ $address->city }}, {{ $address->state }} — {{ $address->pincode }}
                                    </p>
                                    <p class="mt-2 text-xs text-muted">📞 {{ $address->phone }}</p>
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <button type="button" wire:click="$set('showNewAddress', true)"
                            class="mt-5 inline-flex items-center gap-2 rounded-full border border-dashed border-ink/30 px-6 py-3 text-sm font-bold text-ink transition hover:border-brand hover:text-brand">
                        <span aria-hidden="true">＋</span> Add a new address
                    </button>

                    {{-- New address form --}}
                    @if($showNewAddress)
                        <form wire:submit="saveNewAddress" class="mt-6 rounded-3xl border border-ink/10 bg-white p-6 sm:p-8">
                            <h2 class="font-playfair text-lg font-bold text-ink">New delivery address</h2>
                            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                                <label class="block">
                                    <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">Full name</span>
                                    <input type="text" wire:model="name" placeholder="Anoop Puri"
                                           class="h-11 w-full rounded-xl border border-ink/15 bg-paper px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25">
                                    @error('name') <span class="mt-1 block text-xs text-brand">{{ $message }}</span> @enderror
                                </label>
                                <label class="block">
                                    <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">Phone</span>
                                    <input type="tel" wire:model="phone" placeholder="98765 43210"
                                           class="h-11 w-full rounded-xl border border-ink/15 bg-paper px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25">
                                    @error('phone') <span class="mt-1 block text-xs text-brand">{{ $message }}</span> @enderror
                                </label>
                                <label class="block sm:col-span-2">
                                    <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">Address line 1</span>
                                    <input type="text" wire:model="line1" placeholder="House, street, area"
                                           class="h-11 w-full rounded-xl border border-ink/15 bg-paper px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25">
                                    @error('line1') <span class="mt-1 block text-xs text-brand">{{ $message }}</span> @enderror
                                </label>
                                <label class="block sm:col-span-2">
                                    <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">Address line 2 (optional)</span>
                                    <input type="text" wire:model="line2" placeholder="Landmark, building"
                                           class="h-11 w-full rounded-xl border border-ink/15 bg-paper px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25">
                                </label>
                                <label class="block">
                                    <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">City</span>
                                    <input type="text" wire:model="city" placeholder="New Delhi"
                                           class="h-11 w-full rounded-xl border border-ink/15 bg-paper px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25">
                                    @error('city') <span class="mt-1 block text-xs text-brand">{{ $message }}</span> @enderror
                                </label>
                                <label class="block">
                                    <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">State</span>
                                    <input type="text" wire:model="state" placeholder="Delhi"
                                           class="h-11 w-full rounded-xl border border-ink/15 bg-paper px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25">
                                    @error('state') <span class="mt-1 block text-xs text-brand">{{ $message }}</span> @enderror
                                </label>
                                <label class="block">
                                    <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">PIN code</span>
                                    <input type="text" wire:model="pincode" placeholder="110001" maxlength="6" inputmode="numeric"
                                           class="h-11 w-full rounded-xl border border-ink/15 bg-paper px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25">
                                    @error('pincode') <span class="mt-1 block text-xs text-brand">{{ $message }}</span> @enderror
                                </label>
                                <label class="flex items-center gap-2.5 self-end pb-2 text-sm text-ink">
                                    <input type="checkbox" wire:model="isDefault" class="h-4 w-4 rounded border-ink/20 text-brand accent-brand focus:ring-brand/40">
                                    Make this my default address
                                </label>
                            </div>
                            <div class="mt-6 flex flex-wrap gap-3">
                                <button type="submit" wire:loading.attr="disabled"
                                        class="inline-flex items-center gap-2 rounded-full bg-brand px-7 py-3 text-sm font-bold text-white transition hover:bg-brand-dark disabled:opacity-60">
                                    <span wire:loading.remove>Save & continue</span>
                                    <span wire:loading>Saving…</span>
                                </button>
                                <button type="button" wire:click="$set('showNewAddress', false)"
                                        class="rounded-full border border-ink/15 px-6 py-3 text-sm font-semibold text-ink transition hover:border-brand hover:text-brand">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    @endif
                </section>
            @endif

            {{-- ===== STEP 2 · PAYMENT ===== --}}
            @if($step === 2)
                <section aria-label="Payment">
                    <div class="mb-6 flex items-center gap-3 rounded-2xl border border-ink/10 bg-white p-4">
                        <button type="button" wire:click="backToAddresses" class="rounded-full border border-ink/15 px-4 py-2 text-xs font-bold text-ink transition hover:border-brand hover:text-brand">
                            ← Change
                        </button>
                        <div>
                            <p class="text-sm font-bold text-ink">{{ $addresses->firstWhere('id', $addressId)?->name }}</p>
                            <p class="text-xs text-muted">
                                {{ $addresses->firstWhere('id', $addressId)?->line1 }},
                                {{ $addresses->firstWhere('id', $addressId)?->city }}
                                — {{ $addresses->firstWhere('id', $addressId)?->pincode }}
                            </p>
                        </div>
                    </div>

                    @if($paymentError)
                        <p class="mb-5 rounded-xl bg-brand/10 px-4 py-3 text-sm font-semibold text-brand" role="alert">{{ $paymentError }}</p>
                    @endif

                    <div class="rounded-3xl border border-ink/10 bg-white p-6 sm:p-8">
                        <h2 class="flex items-center gap-2 font-playfair text-lg font-bold text-ink">
                            <svg class="h-5 w-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            Payment
                        </h2>
                        <p class="mt-2 text-sm text-muted">
                            @if($razorpayConfigured)
                                You will be redirected to Razorpay's secure checkout (UPI, cards, netbanking, wallets).
                            @else
                                Test mode — no payment gateway keys are configured, so payment is simulated.
                            @endif
                        </p>

                        {{-- Razorpay script (only when configured) --}}
                        @if($razorpayConfigured)
                            <script src="https://checkout.razorpay.com/v1/checkout.js" data-razorpay-key="{{ config('rythme.razorpay.key_id') }}" defer></script>
                        @endif

                        <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
                            @foreach(['UPI', 'Cards', 'Netbanking', 'Wallets'] as $method)
                                <div class="flex flex-col items-center gap-1.5 rounded-xl border border-ink/10 bg-paper px-3 py-4 text-center">
                                    <span class="text-xl" aria-hidden="true">{{ ['UPI' => '📱', 'Cards' => '💳', 'Netbanking' => '🏦', 'Wallets' => '👛'][$method] }}</span>
                                    <span class="text-[11px] font-semibold text-ink">{{ $method }}</span>
                                </div>
                            @endforeach
                        </div>

                        <button type="button" wire:click="placeOrder" wire:loading.attr="disabled" wire:target="placeOrder,confirmPayment"
                                class="mt-7 inline-flex w-full items-center justify-center gap-2 rounded-full bg-brand py-4 text-sm font-bold text-white shadow-[0_12px_30px_rgba(213,8,8,0.25)] transition hover:bg-brand-dark disabled:cursor-not-allowed disabled:opacity-60"
                                aria-label="Pay ₹{{ number_format($totals['subtotal']) }}">
                            <span wire:loading.remove wire:target="placeOrder,confirmPayment">
                                Pay ₹{{ number_format($totals['subtotal']) }} securely
                            </span>
                            <span wire:loading wire:target="placeOrder,confirmPayment" class="inline-flex items-center gap-2">
                                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                                Processing…
                            </span>
                        </button>

                        <p class="mt-4 flex items-center justify-center gap-1.5 text-xs text-muted">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            Secured with 256-bit encryption · Razorpay Trusted Business
                        </p>
                    </div>
                </section>
            @endif
        </div>

        {{-- ===== ORDER SUMMARY ===== --}}
        <aside class="lg:sticky lg:top-28 lg:self-start">
            <div class="rounded-3xl border border-ink/10 bg-white p-6 sm:p-7">
                <h2 class="text-xs font-bold uppercase tracking-[0.2em] text-muted">Order summary</h2>
                <div class="mt-5 max-h-72 space-y-4 overflow-y-auto pr-1">
                    @forelse($cartItems as $item)
                        <div class="flex items-center gap-3">
                            <div class="relative h-14 w-14 shrink-0 overflow-hidden rounded-xl border border-ink/10 bg-paper p-1.5">
                                @if($item->product->getFirstMediaUrl('gallery'))
                                    <img src="{{ $item->product->getFirstMediaUrl('gallery') }}" alt="" class="h-full w-full object-contain">
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-[8px] font-bold uppercase text-muted">{{ $item->product->brand?->name ?? 'R' }}</div>
                                @endif
                                <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-ink px-1 text-[10px] font-bold text-white">{{ $item->qty }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-ink">{{ $item->product->name }}</p>
                                @if($item->variant)<p class="text-xs text-muted">{{ $item->variant->name }}</p>@endif
                            </div>
                            <p class="text-sm font-bold text-ink">₹{{ number_format((float) $item->unit_price * $item->qty) }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-muted">Your cart is empty.</p>
                    @endforelse
                </div>

                <dl class="mt-5 space-y-3 border-t border-ink/10 pt-5 text-sm">
                    <div class="flex items-center justify-between">
                        <dt class="text-ink/70">Subtotal</dt>
                        <dd class="font-semibold text-ink">₹{{ number_format($totals['subtotal']) }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-ink/70">Shipping</dt>
                        <dd class="font-semibold text-emerald-600">FREE</dd>
                    </div>
                    <div class="flex items-center justify-between border-t border-ink/10 pt-3">
                        <dt class="font-bold text-ink">Total</dt>
                        <dd class="text-2xl font-bold text-ink">₹{{ number_format($totals['subtotal']) }}</dd>
                    </div>
                </dl>
            </div>
        </aside>
    </div>
</div>
