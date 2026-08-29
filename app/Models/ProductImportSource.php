<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('product_import_sources')]
#[Fillable(['product_id', 'source', 'source_product_id', 'source_url', 'payload_hash', 'publication_review_required', 'publication_review_reasons', 'publication_reviewed_at', 'publication_reviewed_by', 'commercial_use_approved_at', 'commercial_use_approved_by', 'imported_at'])]
final class ProductImportSource extends Model
{
    protected $casts = [
        'publication_review_required' => 'boolean',
        'publication_review_reasons' => 'array',
        'publication_reviewed_at' => 'immutable_datetime',
        'commercial_use_approved_at' => 'immutable_datetime',
        'imported_at' => 'immutable_datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function publicationReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'publication_reviewed_by');
    }

    public function commercialUseApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'commercial_use_approved_by');
    }
}
