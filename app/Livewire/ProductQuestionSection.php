<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Product;
use App\Services\ProductQuestionService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;

final class ProductQuestionSection extends Component
{
    use WithPagination;

    public Product $product;

    public string $question = '';

    public ?string $error = null;

    public bool $submitted = false;

    public function submit(ProductQuestionService $questions): void
    {
        $this->error = null;

        if (auth()->guest()) {
            $this->redirect(route('login'));

            return;
        }

        $this->validate([
            'question' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        try {
            $this->guardRateLimit();
            $questions->submit((int) auth()->id(), $this->product, $this->question);
            $this->question = '';
            $this->submitted = true;
        } catch (RuntimeException $exception) {
            $this->error = $exception->getMessage();
        }
    }

    public function render(ProductQuestionService $questions): View
    {
        return view('livewire.product-question-section', [
            'questions' => $questions->publishedFor($this->product),
        ]);
    }

    private function guardRateLimit(): void
    {
        $key = 'question:submit:user:'.auth()->id().':product:'.$this->product->getKey();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw new RuntimeException('Too many question attempts. Please wait a moment and try again.');
        }

        RateLimiter::hit($key, 60);
    }
}
