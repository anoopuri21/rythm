<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\GstQuote;
use App\Support\IndiaStates;
use RuntimeException;

final class GstCalculator
{
    public function __construct(private readonly SiteSettingsService $settings) {}

    public function isEnabled(): bool
    {
        return $this->settings->get('tax_rules_enabled', '0') === '1';
    }

    public function originState(): ?string
    {
        $state = trim((string) $this->settings->get('origin_state', ''));

        return $state === '' ? null : $state;
    }

    public function defaultRate(): float
    {
        return $this->settings->getFloat('tax_rate', 0.0);
    }

    public function quote(int $taxableCents, ?float $productRate, ?string $destinationState): GstQuote
    {
        if (! $this->isEnabled()) {
            return GstQuote::none();
        }

        $rate = $productRate === null ? $this->defaultRate() : $productRate;
        if ($rate < 0 || $rate > 100) {
            throw new RuntimeException('GST rate must be between 0 and 100.');
        }

        $origin = $this->originState();
        $destination = trim((string) $destinationState);
        $destination = $destination === '' ? null : $destination;

        if ($rate <= 0 || $taxableCents <= 0) {
            return new GstQuote(
                enabled: true,
                supplyType: $this->supplyType($origin, $destination),
                rate: $rate,
                taxable: $taxableCents / 100,
                cgst: 0.0,
                sgst: 0.0,
                igst: 0.0,
                total: 0.0,
                originState: $origin,
                destinationState: $destination,
            );
        }

        $totalTaxCents = (int) round($taxableCents * ($rate / 100));
        $supplyType = $this->supplyType($origin, $destination);

        if ($supplyType === 'inter') {
            return new GstQuote(
                enabled: true,
                supplyType: 'inter',
                rate: $rate,
                taxable: $taxableCents / 100,
                cgst: 0.0,
                sgst: 0.0,
                igst: $totalTaxCents / 100,
                total: $totalTaxCents / 100,
                originState: $origin,
                destinationState: $destination,
            );
        }

        $cgstCents = intdiv($totalTaxCents, 2);
        $sgstCents = $totalTaxCents - $cgstCents;

        return new GstQuote(
            enabled: true,
            supplyType: 'intra',
            rate: $rate,
            taxable: $taxableCents / 100,
            cgst: $cgstCents / 100,
            sgst: $sgstCents / 100,
            igst: 0.0,
            total: $totalTaxCents / 100,
            originState: $origin,
            destinationState: $destination,
        );
    }

    public function quoteAmount(float $taxableAmount, ?float $productRate, ?string $destinationState): GstQuote
    {
        return $this->quote((int) round($taxableAmount * 100), $productRate, $destinationState);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\CartItem>  $items
     * @param  array<int, float>  $unitPrices
     * @return array{snapshots: array<int, array<string, mixed>>, quote: GstQuote}
     */
    public function snapshotsFor($items, array $unitPrices, float $discount, ?string $destinationState): array
    {
        $subtotalCents = (int) round($items->sum(
            fn ($item): float => $unitPrices[$item->id] * $item->qty,
        ) * 100);
        $discountCentsTotal = min($subtotalCents, max(0, (int) round($discount * 100)));
        $remainingDiscountCents = $discountCentsTotal;
        $snapshots = [];
        $lastIndex = $items->count() - 1;
        $cgst = 0.0;
        $sgst = 0.0;
        $igst = 0.0;
        $taxable = 0.0;
        $supplyType = 'none';
        $rate = 0.0;
        $enabled = $this->isEnabled();

        foreach ($items->values() as $index => $item) {
            $grossCents = (int) round($unitPrices[$item->id] * $item->qty * 100);
            $discountCents = $index === $lastIndex
                ? $remainingDiscountCents
                : min($remainingDiscountCents, (int) round(
                    $subtotalCents > 0 ? $discountCentsTotal * ($grossCents / $subtotalCents) : 0,
                ));
            $remainingDiscountCents -= $discountCents;
            $taxableCents = max(0, $grossCents - $discountCents);
            $configuredRate = $item->product->tax_rate === null
                ? $this->defaultRate()
                : (float) $item->product->tax_rate;
            $quote = $this->quote($taxableCents, $configuredRate, $destinationState);
            $snapshots[$item->id] = array_merge([
                'hsn_code_snapshot' => $item->product->hsn_code,
                'tax_classification_snapshot' => $item->product->tax_classification,
            ], $quote->toLineSnapshot());
            $cgst += $quote->cgst;
            $sgst += $quote->sgst;
            $igst += $quote->igst;
            $taxable += $quote->taxable;
            if ($quote->enabled) {
                $supplyType = $quote->supplyType;
                $rate = max($rate, $quote->rate);
            }
        }

        $total = round($cgst + $sgst + $igst, 2);

        return [
            'snapshots' => $snapshots,
            'quote' => new GstQuote(
                enabled: $enabled,
                supplyType: $supplyType,
                rate: $rate,
                taxable: round($taxable, 2),
                cgst: round($cgst, 2),
                sgst: round($sgst, 2),
                igst: round($igst, 2),
                total: $total,
                originState: $this->originState(),
                destinationState: $destinationState,
            ),
        ];
    }

    private function supplyType(?string $origin, ?string $destination): string
    {
        if ($origin === null || $destination === null) {
            return 'intra';
        }

        return IndiaStates::same($origin, $destination) ? 'intra' : 'inter';
    }
}
