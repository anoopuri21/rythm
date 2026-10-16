<?php

declare(strict_types=1);

namespace App\Filesystem;

use Cloudinary\Api\Exception\NotFound;
use Cloudinary\Cloudinary;
use League\Flysystem\Config;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToCheckExistence;
use League\Flysystem\UnableToCopyFile;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToMoveFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\UnableToWriteFile;
use League\Flysystem\Visibility;

/**
 * Stores Spatie media on Cloudinary without cloudinary-labs/cloudinary-laravel.
 *
 * That package does not support Laravel 13, so the filesystem driver has to
 * live here. The public id is the disk path minus its extension, which is the
 * same path CloudinaryDeliveryUrl uses to build res.cloudinary.com links.
 */
final class CloudinaryAdapter implements FilesystemAdapter
{
    private ?Cloudinary $cloudinary = null;

    /** @param  array<string, mixed>  $config */
    public function __construct(private readonly array $config) {}

    public static function publicId(string $path): string
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');
        $extension = strrpos($path, '.');
        $slash = strrpos($path, '/');

        if ($extension !== false && ($slash === false || $extension > $slash)) {
            return substr($path, 0, $extension);
        }

        return $path;
    }

    public function fileExists(string $path): bool
    {
        try {
            $this->client()->adminApi()->asset(self::publicId($path), [
                'resource_type' => 'image',
            ]);

            return true;
        } catch (NotFound) {
            return false;
        } catch (\Throwable $exception) {
            throw UnableToCheckExistence::forLocation($path, $exception);
        }
    }

    public function directoryExists(string $path): bool
    {
        return false;
    }

    public function write(string $path, string $contents, Config $config): void
    {
        $temporary = tempnam(sys_get_temp_dir(), 'cld');

        if ($temporary === false) {
            throw UnableToWriteFile::atLocation($path, 'Could not prepare the upload.');
        }

        try {
            if (file_put_contents($temporary, $contents) === false) {
                throw UnableToWriteFile::atLocation($path, 'Could not prepare the upload.');
            }

            $this->client()->uploadApi()->upload($temporary, [
                'public_id' => self::publicId($path),
                'resource_type' => 'image',
                'overwrite' => true,
                'invalidate' => true,
                'unique_filename' => false,
                'use_filename' => false,
            ]);
        } catch (UnableToWriteFile $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw UnableToWriteFile::atLocation($path, $exception->getMessage(), $exception);
        } finally {
            @unlink($temporary);
        }
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        $body = stream_get_contents($contents);

        if ($body === false) {
            throw UnableToWriteFile::atLocation($path, 'Could not read the upload.');
        }

        $this->write($path, $body, $config);
    }

    public function read(string $path): string
    {
        $stream = $this->readStream($path);

        try {
            $contents = stream_get_contents($stream);
        } finally {
            fclose($stream);
        }

        if ($contents === false) {
            throw UnableToReadFile::fromLocation($path);
        }

        return $contents;
    }

    public function readStream(string $path)
    {
        $url = \App\Support\CloudinaryDeliveryUrl::for($path);
        $contents = $url === '' ? false : @file_get_contents($url);

        if ($contents === false) {
            throw UnableToReadFile::fromLocation($path);
        }

        $stream = fopen('php://temp', 'w+b');

        if ($stream === false) {
            throw UnableToReadFile::fromLocation($path);
        }

        fwrite($stream, $contents);
        rewind($stream);

        return $stream;
    }

    public function delete(string $path): void
    {
        try {
            $this->client()->uploadApi()->destroy(self::publicId($path), [
                'resource_type' => 'image',
                'invalidate' => true,
            ]);
        } catch (NotFound) {
            return;
        } catch (\Throwable $exception) {
            throw UnableToDeleteFile::atLocation($path, $exception->getMessage(), $exception);
        }
    }

    public function deleteDirectory(string $path): void {}

    public function createDirectory(string $path, Config $config): void {}

    public function setVisibility(string $path, string $visibility): void {}

    public function visibility(string $path): FileAttributes
    {
        return new FileAttributes($path, visibility: Visibility::PUBLIC);
    }

    public function mimeType(string $path): FileAttributes
    {
        $asset = $this->asset($path);

        return new FileAttributes($path, mimeType: isset($asset['format']) ? 'image/'.$asset['format'] : null);
    }

    public function lastModified(string $path): FileAttributes
    {
        $asset = $this->asset($path);
        $timestamp = isset($asset['created_at']) ? strtotime((string) $asset['created_at']) : false;

        return new FileAttributes($path, lastModified: $timestamp === false ? null : $timestamp);
    }

    public function fileSize(string $path): FileAttributes
    {
        $asset = $this->asset($path);

        return new FileAttributes($path, fileSize: isset($asset['bytes']) ? (int) $asset['bytes'] : null);
    }

    public function listContents(string $path, bool $deep): iterable
    {
        return [];
    }

    public function move(string $source, string $destination, Config $config): void
    {
        try {
            $this->write($destination, $this->read($source), $config);
            $this->delete($source);
        } catch (\Throwable $exception) {
            throw UnableToMoveFile::fromLocationTo($source, $destination, $exception);
        }
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        try {
            $this->write($destination, $this->read($source), $config);
        } catch (\Throwable $exception) {
            throw UnableToCopyFile::fromLocationTo($source, $destination, $exception);
        }
    }

    private function client(): Cloudinary
    {
        if ($this->cloudinary instanceof Cloudinary) {
            return $this->cloudinary;
        }

        $url = trim((string) ($this->config['url'] ?? ''));

        $this->cloudinary = $url !== ''
            ? new Cloudinary($url)
            : new Cloudinary([
                'cloud' => [
                    'cloud_name' => $this->config['cloud'] ?? null,
                    'api_key' => $this->config['key'] ?? null,
                    'api_secret' => $this->config['secret'] ?? null,
                    'secure' => (bool) ($this->config['secure'] ?? true),
                ],
            ]);

        return $this->cloudinary;
    }

    /** @return array<string, mixed> */
    private function asset(string $path): array
    {
        try {
            $response = $this->client()->adminApi()->asset(self::publicId($path), [
                'resource_type' => 'image',
            ]);
        } catch (\Throwable $exception) {
            throw UnableToRetrieveMetadata::create($path, 'file', previous: $exception);
        }

        return $response->getArrayCopy();
    }
}
