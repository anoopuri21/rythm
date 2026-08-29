@extends('layouts.app')

@section('title', 'Notifications — Rhythm Exports')

@section('content')
    <main class="bg-paper">
        <div class="mx-auto max-w-5xl px-5 py-10 sm:px-8 sm:py-14">
            <nav aria-label="Breadcrumb" class="mb-8 flex items-center gap-2 text-xs text-muted">
                <a href="{{ route('home') }}" class="hover:text-brand">Home</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('account.index') }}" class="hover:text-brand">My Account</a>
                <span aria-hidden="true">/</span>
                <span aria-current="page" class="font-semibold text-ink">Notifications</span>
            </nav>

            <div class="flex flex-wrap items-end justify-between gap-5">
                <div>
                    <p class="section-kicker mb-3">Account updates</p>
                    <h1 class="section-title">Notifications</h1>
                    <p class="mt-3 text-sm text-muted"><strong class="text-ink">{{ $unreadCount }}</strong> unread</p>
                </div>
                @if($unreadCount > 0)
                    <form method="POST" action="{{ route('account.notifications.read-all') }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="rounded-full border border-ink/15 px-5 py-2.5 text-sm font-semibold text-ink hover:border-brand hover:text-brand">
                            Mark all as read
                        </button>
                    </form>
                @endif
            </div>

            @if(session('notification_status'))
                <p class="mt-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700" role="status">{{ session('notification_status') }}</p>
            @endif

            <section aria-labelledby="notification-list-heading" class="mt-8">
                <h2 id="notification-list-heading" class="sr-only">Notification history</h2>
                <ol class="space-y-3">
                    @forelse($notifications as $notification)
                        <li class="rounded-2xl border p-5 sm:p-6 {{ $notification->read_at ? 'border-ink/10 bg-white' : 'border-brand/30 bg-brand/5' }}">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="font-bold text-ink">{{ $notification->data['title'] ?? 'Account update' }}</h3>
                                        @if(!$notification->read_at)
                                            <span class="rounded-full bg-brand px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white">Unread</span>
                                        @endif
                                    </div>
                                    <p class="mt-2 text-sm leading-6 text-muted">{{ $notification->data['message'] ?? '' }}</p>
                                    <time datetime="{{ $notification->created_at?->toIso8601String() }}" class="mt-2 block text-xs text-muted">
                                        {{ $notification->created_at?->format('d M Y, h:i A') }}
                                    </time>
                                </div>
                                <form method="POST" action="{{ $notification->read_at ? route('account.notifications.unread', $notification->id) : route('account.notifications.read', $notification->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="shrink-0 text-sm font-semibold text-brand hover:text-brand-dark">
                                        Mark as {{ $notification->read_at ? 'unread' : 'read' }}
                                    </button>
                                </form>
                            </div>
                        </li>
                    @empty
                        <li class="rounded-3xl border border-dashed border-ink/15 bg-white px-6 py-14 text-center">
                            <h3 class="font-playfair text-xl font-bold text-ink">No notifications yet</h3>
                            <p class="mt-2 text-sm text-muted">Transactional order and account updates will appear here.</p>
                        </li>
                    @endforelse
                </ol>
                @if($notifications->hasPages())
                    <nav aria-label="Notification pages" class="mt-8">{{ $notifications->links() }}</nav>
                @endif
            </section>

            <section aria-labelledby="notification-preferences-heading" class="mt-12 rounded-3xl border border-ink/10 bg-white p-6 sm:p-8">
                <h2 id="notification-preferences-heading" class="font-playfair text-2xl font-bold text-ink">Optional preferences</h2>
                <p class="mt-2 text-sm leading-6 text-muted">Payment, refund, security and essential order notifications always remain enabled.</p>
                @if(session('preference_status'))
                    <p class="mt-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700" role="status">{{ session('preference_status') }}</p>
                @endif
                <form method="POST" action="{{ route('account.notifications.preferences') }}" class="mt-6 space-y-5">
                    @csrf
                    @method('PATCH')
                    @foreach($categories as $category)
                        @php($preference = $preferences->get($category))
                        <fieldset class="rounded-2xl border border-ink/10 p-5">
                            <legend class="px-2 text-sm font-bold text-ink">{{ str($category)->replace('_', ' ')->title() }}</legend>
                            <input type="hidden" name="preferences[{{ $category }}][email_enabled]" value="0">
                            <input type="hidden" name="preferences[{{ $category }}][database_enabled]" value="0">
                            <div class="mt-2 flex flex-wrap gap-6">
                                <label class="inline-flex items-center gap-2 text-sm text-ink">
                                    <input type="checkbox" name="preferences[{{ $category }}][email_enabled]" value="1" class="accent-brand" @checked($preference?->email_enabled ?? true)>
                                    Email
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm text-ink">
                                    <input type="checkbox" name="preferences[{{ $category }}][database_enabled]" value="1" class="accent-brand" @checked($preference?->database_enabled ?? true)>
                                    Notification center
                                </label>
                            </div>
                        </fieldset>
                    @endforeach
                    <button type="submit" class="rounded-full bg-brand px-6 py-3 text-sm font-bold text-white hover:bg-brand-dark">Save preferences</button>
                </form>
            </section>
        </div>
    </main>
@endsection
