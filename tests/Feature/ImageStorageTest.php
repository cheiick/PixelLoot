<?php

use App\Support\ImageStorage;
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
