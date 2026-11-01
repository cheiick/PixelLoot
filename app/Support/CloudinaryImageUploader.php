<?php

namespace App\Support;

use Cloudinary\Cloudinary;
use RuntimeException;

class CloudinaryImageUploader
{
    public function __construct(private Cloudinary $cloudinary) {}

    public function upload(string $contents, string $publicId): string
    {
        $dataUri = 'data:image/webp;base64,'.base64_encode($contents);
        $result = $this->cloudinary->uploadApi()->upload($dataUri, [
            'public_id' => $publicId,
            'resource_type' => 'image',
            'format' => 'webp',
        ]);
        $url = $result->offsetGet('secure_url');

        if (! is_string($url) || filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new RuntimeException('Cloudinary n’a pas retourné une URL valide pour l’image téléversée.');
        }

        return $url;
    }
}
