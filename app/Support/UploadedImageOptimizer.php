<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class UploadedImageOptimizer
{
    public function storeWebp(UploadedFile $file, string $directory, int $maxDimension = 1920, int $quality = 82): string
    {
        $imageInfo = getimagesize($file->getRealPath());
        if ($imageInfo === false || $imageInfo[0] * $imageInfo[1] > 16_000_000) {
            throw ValidationException::withMessages([
                'image' => 'Les dimensions de cette image sont invalides ou trop importantes.',
            ]);
        }

        $contents = file_get_contents($file->getRealPath());
        $source = $contents === false ? false : imagecreatefromstring($contents);
        if ($source === false) {
            throw ValidationException::withMessages([
                'image' => 'Le fichier image ne peut pas être traité.',
            ]);
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, $maxDimension / max($width, $height));
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));
        $optimized = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($optimized === false) {
            imagedestroy($source);
            throw new RuntimeException('Impossible de préparer l’image pour la compression.');
        }

        imagealphablending($optimized, false);
        imagesavealpha($optimized, true);
        $transparent = imagecolorallocatealpha($optimized, 0, 0, 0, 127);
        imagefill($optimized, 0, 0, $transparent);

        if (! imagecopyresampled($optimized, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height)) {
            imagedestroy($source);
            imagedestroy($optimized);
            throw new RuntimeException('Impossible de redimensionner l’image téléversée.');
        }

        $bufferLevel = ob_get_level();
        $webp = false;
        ob_start();
        try {
            $encoded = imagewebp($optimized, null, $quality);
            $webp = ob_get_contents();
        } finally {
            while (ob_get_level() > $bufferLevel) {
                ob_end_clean();
            }
            imagedestroy($source);
            imagedestroy($optimized);
        }

        if (! $encoded || $webp === false || $webp === '') {
            throw new RuntimeException('La compression WebP de l’image a échoué.');
        }

        $path = $directory.'/'.Str::uuid().'.webp';
        $diskName = config('filesystems.image_disk', 'public');

        if ($diskName === 'cloudinary') {
            $cloudinary = config('filesystems.disks.cloudinary');
            if (
                empty($cloudinary['cloud'])
                || ! preg_match('/^[A-Za-z0-9-]+$/', $cloudinary['cloud'])
                || empty($cloudinary['key'])
                || empty($cloudinary['secret'])
            ) {
                throw new RuntimeException(
                    'La configuration Cloudinary est incomplète. Vérifiez CLOUDINARY_CLOUD_NAME, CLOUDINARY_KEY et CLOUDINARY_SECRET.'
                );
            }
        }

        if ($diskName === 'cloudinary') {
            return app(CloudinaryImageUploader::class)->upload($webp, substr($path, 0, -strlen('.webp')));
        }

        $disk = Storage::disk($diskName);
        if (! $disk->put($path, $webp)) {
            throw new RuntimeException('Impossible d’enregistrer l’image optimisée.');
        }

        return $path;
    }
}
