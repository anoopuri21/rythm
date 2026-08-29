<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\SanitizedHtml;
use App\Observers\HomepageSectionObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Homepage section content managed from Filament admin (HOME group).
 * Kicker/title/accent render over the blade partial defaults; content
 * holds section-specific rich text (Tiptap HTML).
 */
#[Table('homepage_sections')]
#[Fillable(['section_key', 'kicker', 'title', 'title_accent', 'content', 'sort_order', 'is_active'])]
#[ObservedBy(HomepageSectionObserver::class)]
class HomepageSection extends Model
{
    use HasFactory;

    protected $casts = [
        'content' => SanitizedHtml::class,
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];
}
