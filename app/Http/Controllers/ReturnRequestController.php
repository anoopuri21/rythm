<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ReturnReason;
use App\Models\ReturnRequest;
use App\Models\User;
use App\Services\ReturnRequestService;
use App\Services\SiteSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class ReturnRequestController extends Controller
{
    public function create(Order $order, SiteSettingsService $settings): View|RedirectResponse
    {
        $customer = auth()->user();
        abort_unless($customer instanceof User && $order->user_id === $customer->id, 403);

        $reasons = ReturnReason::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        if ($settings->get('returns_enabled', '0') !== '1'
            || (int) $settings->get('return_window_days', '0') < 1
            || $order->status !== Order::STATUS_DELIVERED
            || $reasons->isEmpty()) {
            return redirect()->route('orders.show', $order)
                ->with('order_error', 'A return request is not currently available for this order.');
        }

        $order->load(['items', 'returnRequests.items']);

        return view('returns.create', [
            'order' => $order,
            'reasons' => $reasons,
            'requestToken' => 'customer:'.Str::uuid(),
        ]);
    }

    public function store(Request $httpRequest, Order $order, ReturnRequestService $returns): RedirectResponse
    {
        $customer = $httpRequest->user();
        abort_unless($customer instanceof User && $order->user_id === $customer->id, 403);

        $validated = $httpRequest->validate([
            'request_token' => ['required', 'string', 'max:100'],
            'return_reason_id' => ['required', 'integer', 'exists:return_reasons,id'],
            'items' => ['required', 'array'],
            'items.*' => ['nullable', 'integer', 'min:0'],
            'customer_note' => ['nullable', 'string', 'max:2000'],
        ]);
        $reason = ReturnReason::query()->findOrFail($validated['return_reason_id']);
        $quantities = collect($validated['items'])
            ->mapWithKeys(fn ($quantity, $itemId): array => [(int) $itemId => (int) ($quantity ?? 0)])
            ->filter(fn (int $quantity): bool => $quantity > 0)
            ->all();

        try {
            $returns->create(
                $order,
                $reason,
                $quantities,
                $validated['request_token'],
                $customer,
                $validated['customer_note'] ?? null,
            );
        } catch (\RuntimeException $exception) {
            return back()->withInput()->withErrors(['return_request' => $exception->getMessage()]);
        }

        return redirect()->route('orders.show', $order)->with('order_success', 'Your return request was recorded for review.');
    }

    public function cancel(ReturnRequest $returnRequest, ReturnRequestService $returns): RedirectResponse
    {
        $customer = auth()->user();
        abort_unless($customer instanceof User && $returnRequest->user_id === $customer->id, 403);

        try {
            $returns->cancelByCustomer($returnRequest, $customer);
        } catch (\RuntimeException $exception) {
            return back()->with('order_error', $exception->getMessage());
        }

        return back()->with('order_success', 'Your return request was cancelled.');
    }
}
