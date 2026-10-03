<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * The single definition of "where an admin-uploaded image lives".
 *
 * Contract (docs/media-architecture.md §7):
 *
 *  1. Files are written to the `uploads` disk — `public/uploads`, a real folder
 *     inside the web root. Serving it needs no symlink, no queue worker and no
 *     media table, so the link cannot drift away from the file.
 *  2. The database stores the ROOT-RELATIVE URL of the file
 *     (`/uploads/products/01J9ZQ….jpg`): what an admin reads in the row is
 *     exactly what the browser requests.
 *  3. URLs are never built from APP_URL, the request host or a signature.
 *
 * Everything that reads or writes an uploaded image goes through this class so
 * the folder, the naming and the URL shape stay identical everywhere.
 */
final class ImageStore
{
    /** Disk the images live on (config/filesystems.php → disks.uploads). */
    public const DISK = 'uploads';

    /** Extensions we are willing to write; the key is the MIME type. */
    private const EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/avif' => 'avif',
    ];

    public static function disk(): Filesystem
    {
        return Storage::disk(self::DISK);
    }

    /** Host-relative base URL of the disk, e.g. `/uploads`. */
    public static function baseUrl(): string
    {
        return rtrim((string) config('filesystems.disks.'.self::DISK.'.url'), '/');
    }

    /**
     * Normalise anything an admin or an import may have stored into the
     * canonical root-relative URL:
     *
     *  - `products/01J9ZQ….jpg` (a disk path)  → `/uploads/products/01J9ZQ….jpg`
     *  - `/uploads/products/…` or `/images/…`  → unchanged
     *  - `https://cdn.example.com/…`           → unchanged (external image)
     *  - `''` / null                           → null
     */
    public static function url(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (preg_match('~^(https?:)?//~', $value) === 1 || str_starts_with($value, '/')) {
            return $value;
        }

        return self::baseUrl().'/'.ltrim($value, '/');
    }

    /**
     * Disk path behind one of our URLs — null for anything we do not own
     * (external URL, committed asset, foreign path), which is also what stops
     * the delete helpers from touching files outside `public/uploads`.
     */
    public static function path(?string $url): ?string
    {
        $url = self::url($url);

        if ($url === null) {
            return null;
        }

        $base = self::baseUrl();

        if ($base !== '' && str_starts_with($url, $base.'/')) {
            $path = ltrim(substr($url, strlen($base)), '/');

            return $path === '' ? null : $path;
        }

        return null;
    }

    public static function exists(?string $url): bool
    {
        $path = self::path($url);

        if ($path === null) {
            return false;
        }

        try {
            return self::disk()->exists($path);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Write an uploaded image to `{directory}/{ulid}.{ext}` and return its URL.
     * The ULID name is unique and free of anything the uploader chose, so there
     * is no path traversal and no collision with an existing file.
     */
    public static function store(UploadedFile $file, string $directory): ?string
    {
        $extension = self::extension($file);

        if ($extension === null) {
            return null;
        }

        $directory = trim($directory, '/');
        $path = ($directory === '' ? '' : $directory.'/').Str::ulid().'.'.$extension;

        self::disk()->putFileAs($directory === '' ? '/' : $directory, $file, basename($path), 'public');

        return self::url($path);
    }

    /**
     * Copy an existing file on the server (an import, a one-off script) into the
     * uploads folder and return its URL — same naming and validation as store().
     */
    public static function storePath(string $absolutePath, string $directory): ?string
    {
        if (! is_file($absolutePath)) {
            return null;
        }

        $mime = strtolower((string) (new \finfo(FILEINFO_MIME_TYPE))->file($absolutePath));
        $extension = self::EXTENSIONS[$mime] ?? null;

        if ($extension === null) {
            return null;
        }

        $directory = trim($directory, '/');
        $path = ($directory === '' ? '' : $directory.'/').Str::ulid().'.'.$extension;

        self::disk()->put($path, (string) file_get_contents($absolutePath), 'public');

        return self::url($path);
    }

    /**
     * The `{name,size,type,url}` shape Filament's file-upload preview expects.
     * Returned even when the file is gone so a broken link stays visible in the
     * panel instead of silently disappearing from the form.
     *
     * @return array{name:string,size:int,type:?string,url:string}|null
     */
    public static function preview(?string $url): ?array
    {
        $url = self::url($url);

        if ($url === null) {
            return null;
        }

        $size = 0;
        $type = null;
        $path = self::path($url);

        if ($path !== null) {
            try {
                $size = (int) self::disk()->size($path);
                $type = self::disk()->mimeType($path);
            } catch (Throwable) {
                // Missing/unreadable file: preview with the stored URL anyway.
            }
        }

        return [
            'name' => basename((string) (parse_url($url, PHP_URL_PATH) ?: $url)),
            'size' => $size,
            'type' => $type,
            'url' => $url,
        ];
    }

    /** Delete one stored image, if it is one of ours. */
    public static function delete(?string $url): void
    {
        $path = self::path($url);

        if ($path === null) {
            return;
        }

        try {
            self::disk()->delete($path);
        } catch (Throwable) {
            // A file that is already gone is not a failure worth stopping a save for.
        }
    }

    /** @param iterable<string|null> $urls */
    public static function deleteMany(iterable $urls): void
    {
        foreach ($urls as $url) {
            self::delete($url);
        }
    }

    /** Whitelisted extension for an uploaded file, or null when it is not one of our image types. */
    private static function extension(UploadedFile $file): ?string
    {
        $mime = strtolower((string) ($file->getMimeType() ?: ''));

        if (isset(self::EXTENSIONS[$mime])) {
            return self::EXTENSIONS[$mime];
        }

        $extension = strtolower((string) ($file->getClientOriginalExtension() ?: (string) $file->guessExtension()));

        return in_array($extension, self::EXTENSIONS, true) ? $extension : null;
    }
}
