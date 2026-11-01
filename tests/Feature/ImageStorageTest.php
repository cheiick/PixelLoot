<?php

use App\Support\CloudinaryImageUploader;
use App\Support\ImageStorage;
use Cloudinary\Api\ApiResponse;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Cloudinary;
use Illuminate\Support\Facades\Storage;

it('keeps Cloudinary URLs intact and deletes the referenced asset', function () {
    Storage::fake('cloudinary');
    Storage::disk('cloudinary')->put('products/photo.webp', 'image');
    $url = 'https://res.cloudinary.com/demo/image/upload/v123456/products/photo.webp';

    expect(ImageStorage::url($url))->toBe($url);

    ImageStorage::delete($url);

    Storage::disk('cloudinary')->assertMissing('products/photo.webp');
});

it('continues to generate public disk URLs for existing local image paths', function () {
    Storage::fake('public');

    expect(ImageStorage::url('products/photo.webp'))
        ->toBe(Storage::disk('public')->url('products/photo.webp'));
});

it('uploads image data as a Cloudinary-supported base64 data URI', function () {
    $uploadApi = Mockery::mock(UploadApi::class);
    $uploadApi->shouldReceive('upload')
        ->once()
        ->withArgs(function (string $dataUri, array $options): bool {
            return $dataUri === 'data:image/webp;base64,'.base64_encode('RIFFvalid-webp-data')
                && $options === [
                    'public_id' => 'products/photo',
                    'resource_type' => 'image',
                    'format' => 'webp',
                ];
        })
        ->andReturn(new ApiResponse([
            'secure_url' => 'https://res.cloudinary.com/demo/image/upload/products/photo.webp',
        ], []));
    $cloudinary = Mockery::mock(Cloudinary::class);
    $cloudinary->shouldReceive('uploadApi')->once()->andReturn($uploadApi);

    $url = app(CloudinaryImageUploader::class, ['cloudinary' => $cloudinary])
        ->upload('RIFFvalid-webp-data', 'products/photo');

    expect($url)->toBe('https://res.cloudinary.com/demo/image/upload/products/photo.webp');
});
