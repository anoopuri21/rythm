<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Polymorphic on-page SEO — one entry per entity (page, product,
 * category, future blog post). Rendered by SeoService into <head>.
 */
#[Table('seo_entries')]
#[Fillable([
    'seoable_type', 'seoable_id',
    'meta_title', 'meta_description', 'meta_keywords',
    'og_title', 'og_description', 'og_image',
    'canonical_url', 'schema_json', 'head_scripts', 'robots',
])]
class SeoEntry extends Model
{
    use HasFactory;

    protected $casts = [
        'schema_json' => 'array',
    ];

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }
}
