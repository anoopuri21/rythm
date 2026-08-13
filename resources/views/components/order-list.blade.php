@props(['orders'])

<div class="overflow-x-auto rounded-3xl border border-ink/10 bg-white">
    <table class="w-full min-w-[640px] text-left text-sm">
        <thead class="border-b border-ink/10 bg-paper-dark text-xs uppercase tracking-wider text-muted">
            <tr>
                <th class="px-6 py-4 font-bold">Order</th>
                <th class="px-6 py-4 font-bold">Date</th>
                <th class="px-6 py-4 font-bold">Items</th>
                <th class="px-6 py-4 font-bold">Total</th>
                <th class="px-6 py-4 font-bold">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-ink/5">
            @foreach($orders as $order)
                <tr class="transition hover:bg-paper">
                    <td class="px-6 py-4 font-mono text-xs font-bold text-ink">{{ $order->order_number }}</td>
                    <td class="px-6 py-4 text-ink/70">{{ $order->created_at?->format('d M Y') }}</td>
                    <td class="px-6 py-4 text-ink/70">{{ $order->items_count }} {{ Str::plural('item', $order->items_count) }}</td>
                    <td class="px-6 py-4 font-bold text-ink">₹{{ number_format((float) $order->total) }}</td>
                    <td class="px-6 py-4">
                        @php
                            $colors = [
                                'confirmed' => 'bg-emerald-100 text-emerald-700',
                                'paid' => 'bg-emerald-100 text-emerald-700',
                                'pending' => 'bg-amber-100 text-amber-700',
                                'processing' => 'bg-blue-100 text-blue-700',
                                'shipped' => 'bg-blue-100 text-blue-700',
                                'delivered' => 'bg-emerald-100 text-emerald-700',
                                'cancelled' => 'bg-red-100 text-red-700',
                                'failed' => 'bg-red-100 text-red-700',
                            ];
                            $status = $order->status;
                            $badge = $colors[$status] ?? 'bg-ink/10 text-ink/70';
                        @endphp
                        <span class="rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-wide {{ $badge }}">{{ $status }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
