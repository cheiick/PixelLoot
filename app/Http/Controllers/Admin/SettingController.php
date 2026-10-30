<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Support\UploadedImageOptimizer;

class SettingController extends Controller
{
    public function index()
    {
        $defaults = [
            'site_name' => 'MonShop',
            'site_logo' => '',
            'currency_code' => 'EUR',
            'currency_symbol' => '€',
            'hero_title' => 'Gaming & High-Tech',
            'hero_description' => 'Découvrez les derniers jeux, consoles et accessoires tech au meilleur prix. Livraison rapide et garantie incluse.',
            'footer_email' => 'contact@monshop.com',
            'footer_phone' => '01 23 45 67 89',
            'social_facebook' => '',
            'social_instagram' => '',
            'social_whatsapp' => '',
            'social_tiktok' => '',
        ];

        $settings = collect($defaults)
            ->map(fn (string $default, string $key) => SiteSetting::getValue($key, $default))
            ->all();

        $settings['hero_images'] = $this->heroImages();

        return view('admin.settings.index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        if ($request->filled('custom_currency_code')) {
            $request->merge(['custom_currency_code' => strtoupper($request->input('custom_currency_code'))]);
        }

        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:255'],
            'currency' => ['nullable', 'in:EUR,XOF,USD,GBP,CAD,other'],
            'custom_currency_code' => ['required_if:currency,other', 'nullable', 'regex:/^[A-Z]{3}$/'],
            'currency_symbol' => ['nullable', 'string', 'max:8', 'regex:/^[\p{L}\p{Sc} ._-]+$/u'],
            'hero_title' => ['required', 'string', 'max:150'],
            'hero_description' => ['required', 'string', 'max:1000'],
            'site_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=10000,max_height=10000'],
            'remove_site_logo' => ['nullable', 'boolean'],
            'hero_images' => ['sometimes', 'required', 'array', 'min:1', 'max:5'],
            'hero_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:10240', 'dimensions:max_width=10000,max_height=10000'],
            'remove_hero_images' => ['nullable', 'boolean'],
            'footer_email' => ['required', 'email', 'max:255'],
            'footer_phone' => ['required', 'string', 'max:255'],
            'social_facebook' => ['nullable', 'url:http,https', 'max:2048'],
            'social_instagram' => ['nullable', 'url:http,https', 'max:2048'],
            'social_whatsapp' => ['nullable', 'url:http,https', 'max:2048'],
            'social_tiktok' => ['nullable', 'url:http,https', 'max:2048'],
        ]);

        if ($request->boolean('remove_hero_images') && $request->hasFile('hero_images')) {
            throw ValidationException::withMessages([
                'hero_images' => 'Vous ne pouvez pas supprimer la galerie et téléverser de nouvelles images en même temps.',
            ]);
        }

        $validated = array_map(
            static fn (?string $value): string => trim($value ?? ''),
            array_filter(
                $validated,
                static fn ($value, string $key): bool => ! in_array($key, [
                    'site_logo',
                    'remove_site_logo',
                    'hero_images',
                    'remove_hero_images',
                ], true),
                ARRAY_FILTER_USE_BOTH
            )
        );
        if (isset($validated['currency'])) {
            $currencySymbols = ['EUR' => '€', 'XOF' => 'FCFA', 'USD' => '$', 'GBP' => '£', 'CAD' => '$'];
            $validated['currency_code'] = $validated['currency'] === 'other'
                ? strtoupper($validated['custom_currency_code'])
                : $validated['currency'];
            $validated['currency_symbol'] = $validated['currency_symbol'] ?: $currencySymbols[$validated['currency_code']] ?? $validated['currency_code'];
        }
        unset($validated['currency'], $validated['custom_currency_code']);

        $previousLogo = SiteSetting::getValue('site_logo');
        $optimizer = app(UploadedImageOptimizer::class);
        $newLogo = $request->file('site_logo')
            ? $optimizer->storeWebp($request->file('site_logo'), 'site', 1200)
            : null;
        $removeLogo = $request->boolean('remove_site_logo');
        $previousHeroImages = $this->heroImages();
        $newHeroImages = [];
        foreach ($request->file('hero_images', []) as $image) {
            $newHeroImages[] = $optimizer->storeWebp($image, 'site');
        }
        $replaceHeroImages = $request->hasFile('hero_images');
        $removeHeroImages = $request->boolean('remove_hero_images');

        if ($newLogo !== null) {
            $validated['site_logo'] = $newLogo;
        } elseif ($removeLogo) {
            $validated['site_logo'] = '';
        }

        if ($replaceHeroImages || $removeHeroImages) {
            $validated['hero_images'] = json_encode($newHeroImages, JSON_THROW_ON_ERROR);
            $validated['hero_image'] = '';
        }

        DB::transaction(function () use ($validated) {
            foreach ($validated as $key => $value) {
                SiteSetting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }
        });

        if ($previousLogo && ($newLogo !== null || $removeLogo)) {
            Storage::disk('public')->delete($previousLogo);
        }

        if ($replaceHeroImages || $removeHeroImages) {
            Storage::disk('public')->delete($previousHeroImages);
        }

        return back()->with('success', 'Paramètres mis à jour avec succès.');
    }

    /**
     * Read the gallery, including the single-image setting used by older versions.
     *
     * @return array<int, string>
     */
    private function heroImages(): array
    {
        $storedImages = SiteSetting::getValue('hero_images');
        $images = json_decode($storedImages, true);

        if (is_array($images)) {
            return array_values(array_filter($images, 'is_string'));
        }

        $legacyImage = SiteSetting::getValue('hero_image');

        return $legacyImage !== '' ? [$legacyImage] : [];
    }
}
