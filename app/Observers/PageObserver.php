<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Page;
use App\Support\PublicContent;

/**
 * Invalidate public page-slug cache whenever CMS pages change.
 */
final class PageObserver
{
    public function saved(Page $page): void
    {
        PublicContent::forgetPageCache();
    }

    public function deleted(Page $page): void
    {
        PublicContent::forgetPageCache();
    }
}
