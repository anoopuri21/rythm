<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\ProductQuestion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;

final class ProductQuestionService
{
    private const MAX_ATTEMPTS = 3;

    private const DECAY_SECONDS = 600;

    public function submit(int $userId, Product $product, string $text): ProductQuestion
    {
        $question = trim($text);
        $length = mb_strlen($question);

        if ($length < 10 || $length > 1000) {
            throw new RuntimeException('Your question must be between 10 and 1,000 characters.');
        }

        $key = "product-question:{$userId}:{$product->id}";
        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            throw new RuntimeException('Too many questions were submitted. Please try again later.');
        }

        RateLimiter::hit($key, self::DECAY_SECONDS);

        return ProductQuestion::create([
            'product_id' => $product->id,
            'user_id' => $userId,
            'question' => $question,
            'status' => ProductQuestion::STATUS_PENDING,
        ]);
    }

    public function publishedFor(Product $product): LengthAwarePaginator
    {
        return ProductQuestion::query()
            ->where('product_id', $product->id)
            ->where('status', ProductQuestion::STATUS_APPROVED)
            ->whereNotNull('answer')
            ->with('user:id,name')
            ->orderByDesc('answered_at')
            ->orderByDesc('created_at')
            ->paginate(6, pageName: 'questionsPage');
    }
}
