<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;

/**
 * Verified reviews: paid/delivered eligibility, moderation and summaries.
 */
final class ReviewService
{
    public function submit(int $userId, Product $product, int $rating, ?string $comment): Review
    {
        $key = "review-submit:user:{$userId}";
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw new RuntimeException('Too many review attempts. Please try again later.');
        }
        RateLimiter::hit($key, 3600);

        if ($rating < 1 || $rating > 5) {
            throw new RuntimeException('Rating must be between 1 and 5.');
        }
        if (mb_strlen((string) $comment) > 2000) {
            throw new RuntimeException('Review comments must not exceed 2000 characters.');
        }

        $purchased = Order::query()
            ->where('user_id', $userId)
            ->where('status', Order::STATUS_DELIVERED)
            ->where('payment_status', Order::PAYMENT_PAID)
            ->whereHas('items', fn ($query) => $query->where('product_id', $product->id))
            ->exists();

        if (! $purchased) {
            throw new RuntimeException('You can only review products from paid, delivered orders.');
        }

        if (Review::query()->where('user_id', $userId)->where('product_id', $product->id)->exists()) {
            throw new RuntimeException('You have already reviewed this product.');
        }

        try {
            return Review::create([
                'product_id' => $product->id,
                'user_id' => $userId,
                'rating' => $rating,
                'comment' => trim((string) $comment) ?: null,
                'status' => Review::STATUS_PENDING,
            ]);
        } catch (QueryException $exception) {
            if (Review::query()->where('user_id', $userId)->where('product_id', $product->id)->exists()) {
                throw new RuntimeException('You have already reviewed this product.', previous: $exception);
            }

            throw $exception;
        }
    }

    public function approvedFor(Product $product): LengthAwarePaginator
    {
        return Review::query()
            ->where('product_id', $product->id)
            ->where('status', Review::STATUS_APPROVED)
            ->where('is_approved', true)
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->paginate(6, pageName: 'reviewsPage');
    }

    /** @return array{avg: float, count: int, stars: array<int,int>} */
    public function summary(Product $product): array
    {
        $reviews = Review::query()
            ->where('product_id', $product->id)
            ->where('status', Review::STATUS_APPROVED)
            ->where('is_approved', true)
            ->get(['rating']);

        $count = $reviews->count();

        return [
            'avg' => $count > 0 ? round((float) $reviews->avg('rating'), 1) : 0.0,
            'count' => $count,
            'stars' => collect(range(1, 5))
                ->mapWithKeys(fn (int $star): array => [$star => $reviews->where('rating', $star)->count()])
                ->all(),
        ];
    }
}
