<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImportSource;
use App\Models\User;
use App\Support\AdminAccess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use RuntimeException;

final class ImportedProductActivationService
{
    public function __construct(private readonly AdminAuditService $audit) {}

    public function approveAndActivate(Product $product, User $actor, string $reason): Product
    {
        Gate::forUser($actor)->authorize('update', $product);
        if (! $actor->hasAdminPermission(AdminAccess::CATALOGUE_MANAGE)) {
            abort(403);
        }
        $reason = trim($reason);
        if (mb_strlen($reason) < 5 || mb_strlen($reason) > 500) {
            throw new RuntimeException('Activation reason must be between 5 and 500 characters.');
        }

        return DB::transaction(function () use ($product, $actor, $reason): Product {
            $locked = Product::query()->lockForUpdate()->findOrFail($product->getKey());
            /** @var ProductImportSource|null $source */
            $source = ProductImportSource::query()->where('product_id', $locked->id)->lockForUpdate()->first();
            if ($source === null) {
                throw new RuntimeException('Only provenance-backed imported products use this approval workflow.');
            }
            if ($locked->is_active) {
                throw new RuntimeException('Imported product is already active.');
            }
            if ((float) $locked->price <= 0) {
                throw new RuntimeException('A verified positive price is required before activation.');
            }
            $hasStock = $locked->stock > 0 || $locked->variants()->where('is_active', true)->where('stock', '>', 0)->exists();
            if (! $hasStock) {
                throw new RuntimeException('Verified real stock is required before activation.');
            }
            $media = $locked->getMedia('gallery');
            if ($media->isEmpty()) {
                throw new RuntimeException('At least one locally managed product image is required before activation.');
            }

            foreach ($media as $item) {
                $item->setCustomProperty('commercial_use_approved', true);
                $item->setCustomProperty('commercial_use_approved_at', now()->toIso8601String());
                $item->setCustomProperty('commercial_use_approved_by', $actor->id);
                $item->save();
            }

            $source->forceFill([
                'publication_reviewed_at' => now(),
                'publication_reviewed_by' => $actor->id,
                'commercial_use_approved_at' => now(),
                'commercial_use_approved_by' => $actor->id,
            ])->save();

            request()->merge(['audit_reason' => $reason]);
            $locked->update(['is_active' => true]);

            $this->audit->record(
                $actor,
                'catalogue.imported_product_activated',
                $locked,
                ['active' => false, 'reviewed' => false, 'commercial_use_approved' => false],
                ['active' => true, 'reviewed' => true, 'commercial_use_approved' => true],
                $reason,
            );

            return $locked->refresh();
        });
    }
}
