<?php

declare(strict_types=1);

namespace App\DTOs;

final class GstQuote
{
    public function __construct(
        public readonly bool $enabled,
        public readonly string $supplyType,
        public readonly float $rate,
        public readonly float $taxable,
        public readonly float $cgst,
        public readonly float $sgst,
        public readonly float $igst,
        public readonly float $total,
        public readonly ?string $originState,
        public readonly ?string $destinationState,
    ) {}

    public static function none(): self
    {
        return new self(
            enabled: false,
            supplyType: 'none',
            rate: 0.0,
            taxable: 0.0,
            cgst: 0.0,
            sgst: 0.0,
            igst: 0.0,
            total: 0.0,
            originState: null,
            destinationState: null,
        );
    }

    /** @return array<string, mixed> */
    public function toLineSnapshot(): array
    {
        return [
            'tax_rate_snapshot' => $this->enabled ? $this->rate : null,
            'taxable_amount_snapshot' => $this->enabled ? $this->taxable : null,
            'tax_amount_snapshot' => $this->total,
            'cgst_amount_snapshot' => $this->enabled ? $this->cgst : null,
            'sgst_amount_snapshot' => $this->enabled ? $this->sgst : null,
            'igst_amount_snapshot' => $this->enabled ? $this->igst : null,
            'gst_supply_type_snapshot' => $this->enabled ? $this->supplyType : null,
            'tax_calculation_enabled_snapshot' => $this->enabled,
            'tax_destination_region_snapshot' => $this->enabled ? $this->destinationState : null,
            'tax_origin_region_snapshot' => $this->enabled ? $this->originState : null,
        ];
    }
}
