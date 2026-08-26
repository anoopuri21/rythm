<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('product_import_sources')]
#[Fillable(['product_id', 'source', 'source_product_id', 'source_url', 'payload_hash', 'imported_at'])]
final class ProductImportSource extends Model
{
    protected $casts = ['imported_at' => 'immutable_datetime'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
