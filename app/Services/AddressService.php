<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Address;
use Illuminate\Support\Collection;

/**
 * User address book: CRUD + default handling. Orders snapshot the address
 * (JSON), so later edits never mutate placed orders.
 */
final class AddressService
{
    public function forUser(?int $userId): Collection
    {
        if ($userId === null) {
            return collect();
        }

        return Address::query()
            ->where('user_id', $userId)
            ->orderByDesc('is_default')
            ->orderByDesc('updated_at')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function store(int $userId, array $data): Address
    {
        $data['user_id'] = $userId;
        $data['type'] = $data['type'] ?? 'shipping';
        $data['country'] = $data['country'] ?? 'IN';

        if (! empty($data['is_default'])) {
            $this->clearDefault($userId);
        }

        return Address::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Address $address, int $userId, array $data): Address
    {
        if ($address->user_id !== $userId) {
            abort(403);
        }

        if (! empty($data['is_default'])) {
            $this->clearDefault($userId);
        }

        $address->update($data);

        return $address->fresh();
    }

    public function delete(Address $address, int $userId): void
    {
        if ($address->user_id !== $userId) {
            abort(403);
        }

        $address->delete();
    }

    public function setDefault(Address $address, int $userId): void
    {
        if ($address->user_id !== $userId) {
            abort(403);
        }

        $this->clearDefault($userId);
        $address->update(['is_default' => true]);
    }

    /** @return array<string, mixed> immutable snapshot for orders */
    public function snapshot(Address $address): array
    {
        return [
            'name' => $address->name,
            'phone' => $address->phone,
            'email' => $address->email,
            'line1' => $address->line1,
            'line2' => $address->line2,
            'city' => $address->city,
            'state' => $address->state,
            'pincode' => $address->pincode,
            'country' => $address->country,
        ];
    }

    private function clearDefault(int $userId): void
    {
        Address::where('user_id', $userId)->where('is_default', true)->update(['is_default' => false]);
    }
}
