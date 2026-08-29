@extends('layouts.app')

@section('title', 'My Account — Rhythm Exports')

@section('content')
    <div class="bg-paper" x-data="{ tab: 'overview' }">
        <div class="mx-auto max-w-7xl px-5 py-10 sm:px-8 sm:py-14 lg:px-12">
            <nav aria-label="Breadcrumb" class="mb-8 flex items-center gap-2 text-xs text-muted">
                <a href="{{ route('home') }}" class="transition hover:text-brand">Home</a>
                <span aria-hidden="true" class="text-ink/30">/</span>
                <span class="font-semibold text-ink" aria-current="page">My Account</span>
            </nav>

            {{-- Header --}}
            <div class="flex flex-wrap items-center justify-between gap-6">
                <div>
                    <p class="section-kicker mb-3">Welcome back</p>
                    <h1 class="section-title">{{ auth()->user()->name }}</h1>
                    <p class="mt-3 text-sm text-muted">{{ auth()->user()->email }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('account.notifications.index') }}" class="rounded-full border border-ink/15 px-6 py-2.5 text-sm font-semibold text-ink transition hover:border-brand hover:text-brand">
                        Notifications
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-full border border-ink/15 px-6 py-2.5 text-sm font-semibold text-ink transition hover:border-brand hover:text-brand">
                            Sign out
                        </button>
                    </form>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="mt-10 flex flex-wrap gap-2 border-b border-ink/10" role="tablist" aria-label="Account sections">
                @foreach([
                    'overview' => ['Overview', 'heroicon-o-home'],
                    'orders' => ['Orders', 'heroicon-o-shopping-bag'],
                    'addresses' => ['Addresses', 'heroicon-o-map-pin'],
                    'support' => ['Support', 'heroicon-o-chat-bubble-left-right'],
                    'settings' => ['Settings', 'heroicon-o-cog-6-tooth'],
                ] as $key => [$label, $icon])
                    <button type="button" role="tab" id="account-tab-{{ $key }}" aria-controls="account-panel-{{ $key }}"
                            :aria-selected="tab === '{{ $key }}' ? 'true' : 'false'"
                            @click="tab = '{{ $key }}'"
                            class="-mb-px border-b-2 px-5 py-3 text-sm font-bold transition"
                            :class="tab === '{{ $key }}' ? 'border-brand text-brand' : 'border-transparent text-muted hover:text-ink'">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            {{-- ===== OVERVIEW ===== --}}
            <section x-show="tab === 'overview'" id="account-panel-overview" role="tabpanel" aria-labelledby="account-tab-overview" class="py-10">
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    @foreach([
                        ['Orders', $orders->count(), '/'],
                        ['Wishlist', $wishlistCount, '/wishlist'],
                        ['Addresses', $addresses->count(), '#addresses'],
                        ['Member since', auth()->user()->created_at?->format('Y'), '/'],
                    ] as [$label, $value, $href])
                        <div class="rounded-3xl border border-ink/10 bg-white p-6 text-center">
                            <p class="font-playfair text-3xl font-bold text-brand sm:text-4xl">{{ $value }}</p>
                            <p class="mt-2 text-xs font-semibold uppercase tracking-[0.18em] text-muted">{{ $label }}</p>
                        </div>
                    @endforeach
                </div>

                @if($orders->isNotEmpty())
                    <div class="mt-10">
                        <div class="mb-5 flex items-end justify-between">
                            <h2 class="font-playfair text-2xl font-bold text-ink">Recent orders</h2>
                            <button type="button" @click="tab = 'orders'" class="text-link text-sm">View all <span aria-hidden="true">→</span></button>
                        </div>
                        <x-order-list :orders="$orders" />
                    </div>
                @else
                    <div class="mt-10 flex flex-col items-center rounded-3xl border border-dashed border-ink/15 bg-white px-6 py-16 text-center">
                        <p class="text-5xl" aria-hidden="true">🎵</p>
                        <h2 class="mt-5 font-playfair text-xl font-bold text-ink">No orders yet</h2>
                        <p class="mt-2 max-w-sm text-sm text-muted">Your next instrument is one click away.</p>
                        <a href="{{ route('shop.index') }}" class="mt-6 inline-flex items-center gap-2 rounded-full bg-brand px-7 py-3 text-sm font-bold text-white transition hover:bg-brand-dark">
                            Start shopping <span aria-hidden="true">→</span>
                        </a>
                    </div>
                @endif
            </section>

            {{-- ===== ORDERS ===== --}}
            <section x-show="tab === 'orders'" x-cloak id="account-panel-orders" role="tabpanel" aria-labelledby="account-tab-orders" class="py-10">
                <h2 class="font-playfair text-2xl font-bold text-ink">Order history</h2>
                @if($orders->isNotEmpty())
                    <div class="mt-6">
                        <x-order-list :orders="$orders" />
                        <div class="mt-8">{{ $orders->links() }}</div>
                    </div>
                @else
                    <p class="mt-6 rounded-2xl border border-dashed border-ink/15 bg-white px-6 py-12 text-center text-sm text-muted">
                        You have not placed any orders yet.
                    </p>
                @endif
            </section>

            {{-- ===== ADDRESSES ===== --}}
            <section x-show="tab === 'addresses'" x-cloak id="account-panel-addresses" role="tabpanel" aria-labelledby="account-tab-addresses" class="py-10">
                <div class="flex items-end justify-between gap-4">
                    <h2 class="font-playfair text-2xl font-bold text-ink">Saved addresses</h2>
                    <button type="button" @click="$refs.newAddress.scrollIntoView({behavior:'smooth'})" class="text-link text-sm">Add new <span aria-hidden="true">＋</span></button>
                </div>

                @if(session('address_success'))
                    <p class="mt-5 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700" role="status">{{ session('address_success') }}</p>
                @endif

                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse($addresses as $address)
                        <div class="relative rounded-3xl border border-ink/10 bg-white p-6">
                            @if($address->is_default)
                                <span class="absolute right-5 top-5 rounded-full bg-brand/10 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-brand">Default</span>
                            @endif
                            <p class="font-bold text-ink">{{ $address->name }}</p>
                            <p class="mt-2 text-sm leading-6 text-muted">
                                {{ $address->line1 }}{{ $address->line2 ? ', '.$address->line2 : '' }}<br>
                                {{ $address->city }}, {{ $address->state }} — {{ $address->pincode }}
                            </p>
                            <p class="mt-2 text-xs text-muted">📞 {{ $address->phone }}</p>
                            <div class="mt-4 flex flex-wrap items-center gap-4">
                                @if(!$address->is_default)
                                    <form method="POST" action="{{ route('account.addresses.default', $address) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-xs font-semibold text-muted transition hover:text-brand">Make default</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('account.addresses.destroy', $address) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-muted transition hover:text-brand">Remove</button>
                                </form>
                            </div>
                            <details class="mt-4 border-t border-ink/10 pt-4">
                                <summary class="cursor-pointer text-xs font-semibold text-brand">Edit address</summary>
                                <form method="POST" action="{{ route('account.addresses.update', $address) }}" class="mt-4 grid gap-3">
                                    @csrf
                                    @method('PATCH')
                                    @foreach([
                                        'name' => ['Full name', $address->name],
                                        'phone' => ['Phone', $address->phone],
                                        'line1' => ['Address line 1', $address->line1],
                                        'line2' => ['Address line 2', $address->line2],
                                        'city' => ['City', $address->city],
                                        'state' => ['State', $address->state],
                                        'pincode' => ['PIN code', $address->pincode],
                                    ] as $field => [$label, $value])
                                        <label class="block">
                                            <span class="mb-1 block text-[10px] font-bold uppercase tracking-wide text-muted">{{ $label }}</span>
                                            <input type="text" name="{{ $field }}" value="{{ $value }}" {{ $field === 'line2' ? '' : 'required' }}
                                                   class="h-10 w-full rounded-xl border border-ink/15 bg-paper px-3 text-sm text-ink outline-none focus:border-brand">
                                        </label>
                                    @endforeach
                                    <button type="submit" class="rounded-full bg-ink px-4 py-2 text-xs font-bold text-white transition hover:bg-brand">Save changes</button>
                                </form>
                            </details>
                        </div>
                    @empty
                        <p class="rounded-2xl border border-dashed border-ink/15 bg-white px-6 py-12 text-center text-sm text-muted sm:col-span-2 lg:col-span-3">
                            No saved addresses yet — add one below or at checkout.
                        </p>
                    @endforelse
                </div>

                {{-- New address form --}}
                <div x-ref="newAddress" class="mt-10 rounded-3xl border border-ink/10 bg-white p-6 sm:p-8">
                    <h3 class="font-playfair text-lg font-bold text-ink">Add a new address</h3>
                    <form method="POST" action="{{ route('account.addresses.store') }}" class="mt-5 grid gap-4 sm:grid-cols-2">
                        @csrf
                        <label class="block">
                            <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">Full name</span>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   class="h-11 w-full rounded-xl border border-ink/15 bg-paper px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25">
                            @error('name') <span class="mt-1 block text-xs text-brand">{{ $message }}</span> @enderror
                        </label>
                        <label class="block">
                            <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">Phone</span>
                            <input type="tel" name="phone" value="{{ old('phone') }}" required
                                   class="h-11 w-full rounded-xl border border-ink/15 bg-paper px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25">
                            @error('phone') <span class="mt-1 block text-xs text-brand">{{ $message }}</span> @enderror
                        </label>
                        <label class="block sm:col-span-2">
                            <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">Address line 1</span>
                            <input type="text" name="line1" value="{{ old('line1') }}" required
                                   class="h-11 w-full rounded-xl border border-ink/15 bg-paper px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25">
                            @error('line1') <span class="mt-1 block text-xs text-brand">{{ $message }}</span> @enderror
                        </label>
                        <label class="block sm:col-span-2">
                            <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">Address line 2 (optional)</span>
                            <input type="text" name="line2" value="{{ old('line2') }}"
                                   class="h-11 w-full rounded-xl border border-ink/15 bg-paper px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25">
                        </label>
                        <label class="block">
                            <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">City</span>
                            <input type="text" name="city" value="{{ old('city') }}" required
                                   class="h-11 w-full rounded-xl border border-ink/15 bg-paper px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25">
                            @error('city') <span class="mt-1 block text-xs text-brand">{{ $message }}</span> @enderror
                        </label>
                        <label class="block">
                            <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">State</span>
                            <input type="text" name="state" value="{{ old('state') }}" required
                                   class="h-11 w-full rounded-xl border border-ink/15 bg-paper px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25">
                            @error('state') <span class="mt-1 block text-xs text-brand">{{ $message }}</span> @enderror
                        </label>
                        <label class="block">
                            <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">PIN code</span>
                            <input type="text" name="pincode" value="{{ old('pincode') }}" maxlength="6" inputmode="numeric" required
                                   class="h-11 w-full rounded-xl border border-ink/15 bg-paper px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25">
                            @error('pincode') <span class="mt-1 block text-xs text-brand">{{ $message }}</span> @enderror
                        </label>
                        <label class="flex items-center gap-2.5 self-end pb-2 text-sm text-ink">
                            <input type="checkbox" name="is_default" value="1" class="h-4 w-4 rounded border-ink/20 text-brand accent-brand focus:ring-brand/40">
                            Make default
                        </label>
                        <div class="sm:col-span-2">
                            <button type="submit" class="rounded-full bg-brand px-7 py-3 text-sm font-bold text-white transition hover:bg-brand-dark">Save address</button>
                        </div>
                    </form>
                </div>
            </section>

            {{-- ===== SUPPORT ===== --}}
            <section x-show="tab === 'support'" x-cloak id="account-panel-support" role="tabpanel" aria-labelledby="account-tab-support" class="py-10">
                <h2 class="text-2xl font-bold text-ink">Customer support</h2>
                <p class="mt-3 max-w-2xl text-sm leading-7 text-muted">Choose the shortest route for an order, product, delivery or post-delivery question. Include your order number when one exists.</p>
                <div class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <a href="{{ route('contact') }}" class="ui-card ui-card--interactive p-6">
                        <h3 class="font-semibold text-ink">Contact the team</h3>
                        <p class="mt-2 text-sm leading-6 text-muted">Send a product, account or service question.</p>
                    </a>
                    <a href="{{ route('orders.lookup') }}" class="ui-card ui-card--interactive p-6">
                        <h3 class="font-semibold text-ink">Track an order</h3>
                        <p class="mt-2 text-sm leading-6 text-muted">View the latest recorded order status securely.</p>
                    </a>
                    <a href="/returns" class="ui-card ui-card--interactive p-6">
                        <h3 class="font-semibold text-ink">Return or refund help</h3>
                        <p class="mt-2 text-sm leading-6 text-muted">Review eligibility guidance before submitting a request.</p>
                    </a>
                </div>
            </section>

            {{-- ===== SETTINGS ===== --}}
            <section x-show="tab === 'settings'" x-cloak id="account-panel-settings" role="tabpanel" aria-labelledby="account-tab-settings" class="max-w-2xl py-10">
                <h2 class="font-playfair text-2xl font-bold text-ink">Profile settings</h2>

                @if(session('profile_success'))
                    <p class="mt-5 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700" role="status">{{ session('profile_success') }}</p>
                @endif
                @if(session('password_success'))
                    <p class="mt-5 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700" role="status">{{ session('password_success') }}</p>
                @endif

                {{-- Profile form --}}
                <form method="POST" action="{{ route('account.profile.update') }}" class="mt-6 space-y-4 rounded-3xl border border-ink/10 bg-white p-6 sm:p-8">
                    @csrf
                    @method('PATCH')
                    <h3 class="text-sm font-bold text-ink">Personal details</h3>
                    <label class="block">
                        <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">Name</span>
                        <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required
                               class="h-11 w-full rounded-xl border border-ink/15 bg-paper px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25">
                        @error('name') <span class="mt-1 block text-xs text-brand">{{ $message }}</span> @enderror
                    </label>
                    <label class="block">
                        <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">Email</span>
                        <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required
                               class="h-11 w-full rounded-xl border border-ink/15 bg-paper px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25">
                        @error('email') <span class="mt-1 block text-xs text-brand">{{ $message }}</span> @enderror
                    </label>
                    <button type="submit" class="rounded-full bg-brand px-7 py-3 text-sm font-bold text-white transition hover:bg-brand-dark">Save changes</button>
                </form>

                {{-- Password form --}}
                <form method="POST" action="{{ route('account.password.update') }}" class="mt-6 space-y-4 rounded-3xl border border-ink/10 bg-white p-6 sm:p-8">
                    @csrf
                    @method('PATCH')
                    <h3 class="text-sm font-bold text-ink">Change password</h3>
                    <label class="block">
                        <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">Current password</span>
                        <input type="password" name="current_password" required autocomplete="current-password"
                               class="h-11 w-full rounded-xl border border-ink/15 bg-paper px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25">
                        @error('current_password') <span class="mt-1 block text-xs text-brand">{{ $message }}</span> @enderror
                    </label>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">New password</span>
                            <input type="password" name="password" required autocomplete="new-password"
                                   class="h-11 w-full rounded-xl border border-ink/15 bg-paper px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25">
                            @error('password') <span class="mt-1 block text-xs text-brand">{{ $message }}</span> @enderror
                        </label>
                        <label class="block">
                            <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-muted">Confirm new</span>
                            <input type="password" name="password_confirmation" required autocomplete="new-password"
                                   class="h-11 w-full rounded-xl border border-ink/15 bg-paper px-4 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/25">
                        </label>
                    </div>
                    <button type="submit" class="rounded-full bg-brand px-7 py-3 text-sm font-bold text-white transition hover:bg-brand-dark">Update password</button>
                </form>
            </section>
        </div>
    </div>
@endsection
