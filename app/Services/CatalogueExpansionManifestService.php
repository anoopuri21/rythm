<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class CatalogueExpansionManifestService
{
    /** @return array<string, mixed> */
    public function load(string $path): array
    {
        $resolved = $this->isAbsolutePath($path) ? $path : base_path($path);
        if (! is_file($resolved) || (filesize($resolved) ?: 0) > 262_144) {
            throw new RuntimeException('Expansion manifest is missing or exceeds 256 KiB.');
        }

        $manifest = json_decode((string) file_get_contents($resolved), true, flags: JSON_THROW_ON_ERROR);
        if (! is_array($manifest) || ($manifest['schema_version'] ?? null) !== 1) {
            throw new RuntimeException('Expansion manifest schema version is invalid.');
        }
        if (($manifest['source_host'] ?? null) !== config('catalogue.source.allowed_host')) {
            throw new RuntimeException('Expansion manifest source host is not approved.');
        }

        $groups = $manifest['groups'] ?? null;
        if (! is_array($groups) || $groups === [] || count($groups) > 8) {
            throw new RuntimeException('Expansion manifest must contain between one and eight groups.');
        }

        $collections = [];
        $handles = [];
        foreach ($groups as $group) {
            if (! is_array($group)) {
                throw new RuntimeException('Expansion manifest group is invalid.');
            }
            $collection = (string) ($group['source_collection'] ?? '');
            $category = (string) ($group['category_slug'] ?? '');
            $products = $group['products'] ?? null;
            if (! preg_match('/^[a-z0-9][a-z0-9-]*$/', $collection) || ! preg_match('/^[a-z0-9][a-z0-9-]*$/', $category)) {
                throw new RuntimeException('Expansion manifest collection or category handle is invalid.');
            }
            if (isset($collections[$collection])) {
                throw new RuntimeException("Expansion manifest repeats collection: {$collection}");
            }
            if (! is_array($products) || $products === [] || count($products) > 10 || (int) ($group['target'] ?? 0) !== count($products)) {
                throw new RuntimeException("Expansion manifest group {$collection} must contain its declared target of one to ten products.");
            }

            foreach ($products as $product) {
                $handle = is_array($product) ? (string) ($product['handle'] ?? '') : '';
                if (! preg_match('/^[a-z0-9][a-z0-9-]*$/', $handle) || isset($handles[$handle])) {
                    throw new RuntimeException("Expansion manifest product handle is invalid or duplicated: {$handle}");
                }
                $sourceId = (string) ($product['source_product_id'] ?? '');
                if (! ctype_digit($sourceId) || trim((string) ($product['title'] ?? '')) === '') {
                    throw new RuntimeException("Expansion manifest product identity is incomplete: {$handle}");
                }
                $handles[$handle] = true;
            }

            $collections[$collection] = true;
        }

        if (count($handles) > 80 || (int) ($manifest['target_total'] ?? count($handles)) !== count($handles)) {
            throw new RuntimeException('Expansion manifest target total is invalid or exceeds 80 products.');
        }

        $manifest['_path'] = $resolved;
        $manifest['_sha256'] = hash_file('sha256', $resolved);

        return $manifest;
    }

    private function isAbsolutePath(string $path): bool
    {
        if ($path === '') {
            return false;
        }

        // POSIX root, Windows rooted/UNC path, or Windows drive-qualified path.
        return str_starts_with($path, '/')
            || str_starts_with($path, '\\')
            || (isset($path[2])
                && ctype_alpha($path[0])
                && $path[1] === ':'
                && in_array($path[2], ['/', '\\'], true));
    }
}
