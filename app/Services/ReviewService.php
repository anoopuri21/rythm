<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use RuntimeException;

/**
 * Reviews: verified-purchase submission, moderation, product summaries.
 */
final class ReviewService
{
    /**
     * @throws RuntimeException when the user has not purchased the product
     */
    public function submit(int $userId, Product $product, int $rating, ?string $comment): Review
    {
        if ($rating < 1 || $rating > 5) {
            throw new RuntimeException('Rating must be between 1 and 5.');
        }

        $purchased = Order::query()
            ->where('user_id', $userId)
            ->where('status', '!=', Order::STATUS_CANCELLED)
            ->whereHas('items', fn ($q) => $q->where('product_id', $product->id))
            ->exists();

        if (! $purchased) {
            throw new RuntimeException('You can only review products you have purchased.');
        }

        $existing = Review::query()
            ->where('user_id', $userId)
            ->where('product_id', $product->id)
            ->exists();

        if ($existing) {
            throw new RuntimeException('You have already reviewed this product.');
        }

        return Review::create([
            'product_id' => $product->id,
            'user_id' => $userId,
            'rating' => $rating,
            'comment' => $comment,
            'is_approved' => false,
        ]);
    }

    public function approvedFor(Product $product): LengthAwarePaginator
    {
        return Review::query()
            ->where('product_id', $product->id)
            ->where('is_approved', true)
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->paginate(6);
    }

    /** @return array{avg: float, count: int, stars: array<int,int>} */
    public function summary(Product $product): array
    {
        $reviews = Review::query()
            ->where('product_id', $product->id)
            ->where('is_approved', true)
            ->get(['rating']);

        $count = $reviews->count();

        return [
            'avg' => $count > 0 ? round($reviews->avg('rating'), 1) : 0.0,
            'count' => $count,
            'stars' => collect(range(1, 5))
                ->mapWithKeys(fn (int $star): array => [$star => $reviews->where('rating', $star)->count()])
                ->all(),
        ];
    }
}
