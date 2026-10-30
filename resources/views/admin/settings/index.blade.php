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

        <fieldset class="border-b pb-6 space-y-4">
            <legend class="text-lg font-semibold mb-3">E-mails de commande (SMTP)</legend>
            <p class="text-sm text-gray-600">
                Configurez un fournisseur SMTP, par exemple le SMTP de Brevo (offre gratuite soumise aux limites du fournisseur), ou celui de votre choix.
                Après confirmation du paiement Stripe, le client reçoit le détail de sa commande et son statut. Les changements de statut dans le panel envoient également un e-mail.
                Les identifiants sont chiffrés en base et le mot de passe n’est jamais réaffiché.
            </p>
            <label class="flex items-center gap-2 text-sm font-medium">
                <input type="hidden" name="mail_enabled" value="0">
                <input type="checkbox" name="mail_enabled" value="1" @checked(old('mail_enabled', $settings['mail_enabled']) === '1')>
                Activer l’envoi des e-mails
            </label>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="mail_smtp_host" class="block text-sm font-medium mb-1">Serveur SMTP</label>
                    <input type="text" id="mail_smtp_host" name="mail_smtp_host" value="{{ old('mail_smtp_host', $settings['mail_smtp_host']) }}" placeholder="smtp-relay.brevo.com" maxlength="255" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label for="mail_smtp_port" class="block text-sm font-medium mb-1">Port SMTP</label>
                    <input type="number" id="mail_smtp_port" name="mail_smtp_port" value="{{ old('mail_smtp_port', $settings['mail_smtp_port']) }}" min="1" max="65535" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label for="mail_smtp_encryption" class="block text-sm font-medium mb-1">Chiffrement</label>
                    <select id="mail_smtp_encryption" name="mail_smtp_encryption" class="w-full border rounded px-3 py-2">
                        <option value="tls" @selected(old('mail_smtp_encryption', $settings['mail_smtp_encryption']) === 'tls')>TLS (recommandé, port 587)</option>
                        <option value="ssl" @selected(old('mail_smtp_encryption', $settings['mail_smtp_encryption']) === 'ssl')>SSL (généralement port 465)</option>
                    </select>
                </div>
                <div>
                    <label for="mail_smtp_username" class="block text-sm font-medium mb-1">Identifiant SMTP</label>
                    <input type="text" id="mail_smtp_username" name="mail_smtp_username" value="{{ old('mail_smtp_username', $settings['mail_smtp_username']) }}" maxlength="255" autocomplete="off" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label for="mail_smtp_password" class="block text-sm font-medium mb-1">Mot de passe / clé SMTP</label>
                    <input type="password" id="mail_smtp_password" name="mail_smtp_password" maxlength="4096" autocomplete="new-password" placeholder="{{ $settings['mail_password_configured'] ? 'Déjà configuré — vide pour le conserver' : 'Saisir le mot de passe SMTP' }}" class="w-full border rounded px-3 py-2">
                    @if($settings['mail_password_configured'])
                        <p class="mt-1 text-xs text-green-700">Un identifiant secret est enregistré.</p>
                    @endif
                </div>
                <div>
                    <label for="mail_from_address" class="block text-sm font-medium mb-1">Adresse expéditrice autorisée</label>
                    <input type="email" id="mail_from_address" name="mail_from_address" value="{{ old('mail_from_address', $settings['mail_from_address']) }}" maxlength="255" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label for="mail_from_name" class="block text-sm font-medium mb-1">Nom de l’expéditeur</label>
                    <input type="text" id="mail_from_name" name="mail_from_name" value="{{ old('mail_from_name', $settings['mail_from_name']) }}" maxlength="255" class="w-full border rounded px-3 py-2">
                </div>
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

    @if(session('mail_test_success'))
        <div class="mt-4 rounded border border-green-300 bg-green-50 px-4 py-3 text-green-800">{{ session('mail_test_success') }}</div>
    @endif
    @if(session('mail_test_error'))
        <div class="mt-4 rounded border border-red-300 bg-red-50 px-4 py-3 text-red-800">{{ session('mail_test_error') }}</div>
    @endif
    <form action="{{ route('admin.settings.test-mail') }}" method="POST" class="mt-4 flex flex-wrap items-end gap-3 rounded bg-white p-4 shadow">
        @csrf
        <div class="min-w-64 flex-1">
            <label for="mail_test_email" class="mb-1 block text-sm font-medium">Adresse de réception pour le test</label>
            <input type="email" id="mail_test_email" name="mail_test_email" value="{{ old('mail_test_email') }}" required maxlength="255" class="w-full rounded border px-3 py-2">
        </div>
        <button type="submit" class="rounded bg-slate-700 px-4 py-2 text-white hover:bg-slate-800">Envoyer un e-mail de test</button>
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