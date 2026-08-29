<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Faq;
use App\Models\HomepageSection;
use App\Models\Page;
use App\Models\Product;
use App\Services\RichTextSanitizer;
use Illuminate\Database\Eloquent\Model;

final class SanitizeRichTextObserver
{
    public function __construct(private readonly RichTextSanitizer $sanitizer) {}

    public function saving(Model $model): void
    {
        $attributes = match (true) {
            $model instanceof Product => ['description'],
            $model instanceof Page => ['content'],
            $model instanceof HomepageSection => ['content'],
            $model instanceof Faq => ['answer'],
            default => [],
        };

        foreach ($attributes as $attribute) {
            if ($model->isDirty($attribute)) {
                $model->setAttribute($attribute, $this->sanitizer->sanitize($model->getAttribute($attribute)));
            }
        }
    }
}
