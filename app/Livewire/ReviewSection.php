<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Product;
use App\Services\ReviewService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;

final class ReviewSection extends Component
{
    use WithPagination;

    public Product $product;

    public int $rating = 5;

    public ?string $comment = null;

    public ?string $error = null;

    public bool $submitted = false;

    public function mount(Product $product): void
    {
        $this->product = $product->load('media');
    }

    public function setRating(int $rating): void
    {
        $this->rating = max(1, min(5, $rating));
        $this->error = null;
    }

    public function submit(ReviewService $reviews): void
    {
        $this->error = null;

        if (auth()->guest()) {
            $this->redirect(route('login'));

            return;
        }

        $this->validate([
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $this->guardRateLimit();
            $reviews->submit(auth()->id(), $this->product, $this->rating, $this->comment);
            $this->submitted = true;
            $this->comment = null;
        } catch (RuntimeException $e) {
            $this->error = $e->getMessage();
        }
    }

    public function render(ReviewService $reviews): View
    {
        return view('livewire.review-section', [
            'paginated' => $reviews->approvedFor($this->product),
            'summary' => $reviews->summary($this->product),
        ]);
    }

    private function guardRateLimit(): void
    {
        $key = 'review:submit:user:'.auth()->id().':product:'.$this->product->getKey();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw new RuntimeException('Too many review attempts. Please wait a moment and try again.');
        }

        RateLimiter::hit($key, 60);
    }
}
