<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class ImageStorage
{
    public static function url(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    public static function delete(string|array|null $paths): void
    {
        foreach (is_array($paths) ? $paths : [$paths] as $path) {
            if (! is_string($path) || $path === '') {
                continue;
            }

            $cloudinaryPath = self::cloudinaryPath($path);
            if ($cloudinaryPath !== null) {
                Storage::disk('cloudinary')->delete($cloudinaryPath);
            } elseif (! filter_var($path, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    private static function cloudinaryPath(string $url): ?string
    {
        $parts = parse_url($url);
        if (($parts['host'] ?? null) !== 'res.cloudinary.com' || ! isset($parts['path'])) {
            return null;
        }

        $uploadPosition = strpos($parts['path'], '/image/upload/');
        if ($uploadPosition === false) {
            return null;
        }

        $path = substr($parts['path'], $uploadPosition + strlen('/image/upload/'));
        $path = preg_replace('/^v\d+\//', '', $path);

        return rawurldecode($path);
    }
}
