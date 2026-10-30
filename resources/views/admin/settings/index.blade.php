@extends('layouts.admin')
@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">
    <h1 class="text-2xl font-bold mb-6">Paramètres du site</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow space-y-4">
        @csrf
        @method('PUT')

        <fieldset class="border-b pb-6 space-y-4">
            <legend class="text-lg font-semibold mb-3">Identité du site</legend>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="currency" class="block text-sm font-medium mb-1">Devise de la boutique</label>
                    <select id="currency" name="currency" required class="w-full border rounded px-3 py-2">
                        @foreach(['EUR' => 'Euro (EUR)', 'XOF' => 'Franc CFA BCEAO (XOF)', 'USD' => 'Dollar américain (USD)', 'GBP' => 'Livre sterling (GBP)', 'CAD' => 'Dollar canadien (CAD)', 'other' => 'Autre devise'] as $code => $label)
                            <option value="{{ $code }}" @selected(old('currency', in_array($settings['currency_code'], ['EUR', 'XOF', 'USD', 'GBP', 'CAD'], true) ? $settings['currency_code'] : 'other') === $code)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="custom-currency-code-wrap">
                    <label for="custom_currency_code" class="block text-sm font-medium mb-1">Code devise personnalisé (ISO 4217)</label>
                    <input type="text" id="custom_currency_code" name="custom_currency_code" value="{{ old('custom_currency_code', $settings['currency_code']) }}" maxlength="3" pattern="[A-Za-z]{3}" class="w-full border rounded px-3 py-2 uppercase">
                </div>
                <div>
                    <label for="currency_symbol" class="block text-sm font-medium mb-1">Symbole à afficher</label>
                    <input type="text" id="currency_symbol" name="currency_symbol" value="{{ old('currency_symbol', $settings['currency_symbol']) }}" required maxlength="8" class="w-full border rounded px-3 py-2">
                </div>
            </div>
            <p class="text-sm text-gray-600">La devise choisie sera utilisée dans les prix affichés et pour le paiement Stripe. Les montants des produits ne sont pas convertis automatiquement : ajustez-les si nécessaire. Vérifiez aussi que votre compte Stripe accepte cette devise.</p>
            <div>
                <label for="site_name" class="block text-sm font-medium mb-1">Nom du site</label>
                <input type="text" id="site_name" name="site_name" value="{{ old('site_name', $settings['site_name']) }}" required maxlength="255" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label for="site_logo" class="block text-sm font-medium mb-1">Logo du site (PNG, JPG ou WebP, 2 Mo maximum)</label>
                <input type="file" id="site_logo" name="site_logo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="w-full border rounded px-3 py-2">
                @if($settings['site_logo'])
                    <div class="mt-3 flex items-center gap-4">
                        <img src="{{ asset('storage/' . $settings['site_logo']) }}" alt="Logo actuel du site" class="max-h-16 max-w-48 object-contain">
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="remove_site_logo" value="1">
                            Supprimer le logo actuel
                        </label>
                    </div>
                @endif
            </div>
        </fieldset>

        <fieldset class="border-b pb-6 space-y-4">
            <legend class="text-lg font-semibold mb-3">Bannière d’accueil</legend>
            <div>
                <label for="hero_title" class="block text-sm font-medium mb-1">Titre</label>
                <input type="text" id="hero_title" name="hero_title" value="{{ old('hero_title', $settings['hero_title']) }}" required maxlength="150" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label for="hero_description" class="block text-sm font-medium mb-1">Description</label>
                <textarea id="hero_description" name="hero_description" rows="3" required maxlength="1000" class="w-full border rounded px-3 py-2">{{ old('hero_description', $settings['hero_description']) }}</textarea>
            </div>
            <div>
                <label for="hero_images" class="block text-sm font-medium mb-1">Images du banner (1 à 5 images, PNG, JPG ou WebP, 10 Mo maximum par image)</label>
                <input type="file" id="hero_images" name="hero_images[]" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" multiple class="w-full border rounded px-3 py-2">
                <p class="mt-1 text-sm text-gray-600">Sélectionner de nouvelles images remplace la galerie actuelle. Elles défileront automatiquement toutes les 5 secondes.</p>
                @if(count($settings['hero_images']) > 0)
                    <div class="mt-3 grid grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($settings['hero_images'] as $image)
                            <img src="{{ asset('storage/' . $image) }}" alt="Image actuelle du banner" class="h-32 w-full rounded-lg object-cover">
                        @endforeach
                    </div>
                    <div class="mt-3">
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="remove_hero_images" value="1">
                            Supprimer toute la galerie
                        </label>
                    </div>
                @endif
            </div>
        </fieldset>

        <div>
            <label class="block text-sm font-medium mb-1">Email de contact (footer)</label>
            <input type="email" name="footer_email" value="{{ old('footer_email', $settings['footer_email']) }}" required class="w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Téléphone (footer)</label>
            <input type="text" name="footer_phone" value="{{ old('footer_phone', $settings['footer_phone']) }}" required class="w-full border rounded px-3 py-2">
        </div>

        <fieldset class="border-t pt-4">
            <legend class="text-lg font-semibold mb-3">Réseaux sociaux (facultatif)</legend>
            <p class="text-sm text-gray-600 mb-4">Ajoutez les URL des profils à afficher dans le pied de page. Les champs laissés vides ne seront pas affichés.</p>

            <div class="space-y-4">
                <div>
                    <label for="social_facebook" class="block text-sm font-medium mb-1">Facebook</label>
                    <input type="url" id="social_facebook" name="social_facebook" value="{{ old('social_facebook', $settings['social_facebook']) }}" placeholder="https://www.facebook.com/votre-page" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label for="social_instagram" class="block text-sm font-medium mb-1">Instagram</label>
                    <input type="url" id="social_instagram" name="social_instagram" value="{{ old('social_instagram', $settings['social_instagram']) }}" placeholder="https://www.instagram.com/votre-compte" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label for="social_whatsapp" class="block text-sm font-medium mb-1">WhatsApp</label>
                    <input type="url" id="social_whatsapp" name="social_whatsapp" value="{{ old('social_whatsapp', $settings['social_whatsapp']) }}" placeholder="https://wa.me/33..." class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label for="social_tiktok" class="block text-sm font-medium mb-1">TikTok</label>
                    <input type="url" id="social_tiktok" name="social_tiktok" value="{{ old('social_tiktok', $settings['social_tiktok']) }}" placeholder="https://www.tiktok.com/@votre-compte" class="w-full border rounded px-3 py-2">
                </div>
            </div>
        </fieldset>

        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Enregistrer</button>
    </form>
</div>
<script>
(() => {
    const currencySelect = document.getElementById('currency');
    const customCodeWrap = document.getElementById('custom-currency-code-wrap');
    const customCodeInput = document.getElementById('custom_currency_code');
    const symbolInput = document.getElementById('currency_symbol');
    const symbols = { EUR: '€', XOF: 'FCFA', USD: '$', GBP: '£', CAD: '$' };
    const updateCurrencyFields = () => {
        const isCustom = currencySelect.value === 'other';
        customCodeWrap.hidden = !isCustom;
        customCodeInput.required = isCustom;
        if (!isCustom && symbols[currencySelect.value]) {
            symbolInput.value = symbols[currencySelect.value];
        }
    };
    currencySelect.addEventListener('change', updateCurrencyFields);
    updateCurrencyFields();
})();
</script>
@endsection