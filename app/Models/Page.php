<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Dynamic page — content AND url slug are managed from Filament admin.
 * Homepage SEO lives on the page with slug = null (only one allowed).
 */
#[Table('pages')]
#[Fillable(['slug', 'title', 'template', 'content', 'sort_order', 'is_active'])]
class Page extends Model
{
    use HasFactory;

    /**
     * Slugs owned by hardcoded routes — admin cannot create pages here.
     */
    public const RESERVED_SLUGS = [
        'shop', 'cart', 'login', 'register', 'logout', 'checkout', 'wishlist',
        'newsletter', 'admin', 'payment', 'product', 'api', 'up', 'storage',
        'account', 'search', 'brands',
    ];

    public const TEMPLATES = ['generic', 'about', 'contact'];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function seoEntry(): MorphOne
    {
        return $this->morphOne(SeoEntry::class, 'seoable');
    }
}
