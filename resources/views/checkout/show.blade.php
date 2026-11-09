@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
        <div class="mb-8 flex flex-wrap items-end justify-between gap-5">
            <div>
                <p class="mb-2 text-xs font-extrabold uppercase tracking-[0.2em] text-rose-600">{{ __('Dernière étape') }}</p>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-950 dark:text-white sm:text-4xl">{{ __('Finaliser ma commande') }}</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400">{{ __('Renseignez vos coordonnées et votre adresse pour recevoir votre commande en toute sérénité.') }}</p>
            </div>
            <div class="flex items-center gap-3 rounded-full border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-rose-600 text-xs font-extrabold text-white">2</span>
                {{ __('Paiement sécurisé') }}
            </div>
        </div>

        <div class="mb-8 flex items-center gap-3 text-xs font-bold uppercase tracking-wider text-slate-400 sm:text-sm">
            <a href="{{ route('cart.index') }}" class="transition hover:text-rose-600">{{ __('Mon panier') }}</a>
            <span aria-hidden="true">/</span>
            <span class="text-slate-900 dark:text-white">{{ __('Livraison et paiement') }}</span>
        </div>

        @if($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/40 dark:text-red-200" role="alert">
                <p class="mb-1 font-bold">{{ __('Vérifiez les informations ci-dessous.') }}</p>
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @if(session('coupon_success'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200" role="status">
                {{ session('coupon_success') }}
            </div>
        @endif

        @if(session('coupon_error'))
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/40 dark:text-red-200" role="alert">
                {{ session('coupon_error') }}
            </div>
        @endif

        @if(empty($cart))
            <div class="storefront-form-card p-8 text-center dark:bg-slate-900 sm:p-12">
                <p class="text-slate-600 dark:text-slate-300">{{ __('Votre panier est vide.') }}</p>
                <a href="{{ route('catalog.index') }}" class="storefront-primary-button mt-5 inline-flex min-h-11 items-center justify-center rounded-xl px-5 py-2 font-bold text-white">{{ __('Découvrir le catalogue') }}</a>
            </div>
        @else
            <div class="grid items-start gap-8 lg:grid-cols-[minmax(0,1.25fr)_minmax(340px,0.75fr)] lg:gap-10">
                <form id="checkout-form" action="{{ route('checkout.store') }}" method="POST" class="storefront-form-card p-5 dark:bg-slate-900 sm:p-8">
                    @csrf
                    <div class="mb-7 flex items-start gap-4 border-b border-stone-100 pb-6 dark:border-slate-800">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-300" aria-hidden="true">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 21s7-4.35 7-11a7 7 0 1 0-14 0c0 6.65 7 11 7 11Z"/><circle cx="12" cy="10" r="2.2" stroke-width="1.8"/></svg>
                        </span>
                        <div>
                            <h2 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ __('Coordonnées de livraison') }}</h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Ces informations seront utilisées pour la livraison et le suivi de votre commande.') }}</p>
                        </div>
                    </div>

                    <div class="grid gap-x-5 gap-y-5 sm:grid-cols-2">
                        <fieldset class="sm:col-span-2">
                            <legend class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">{{ __('Mode de paiement') }} <span class="text-rose-600">*</span></legend>
                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-stone-200 p-4 dark:border-slate-700">
                                <input type="radio" name="payment_method" value="stripe" required @checked(old('payment_method', 'stripe') === 'stripe') class="mt-1 accent-rose-600">
                                <span>
                                    <span class="block font-bold text-slate-900 dark:text-white">Stripe</span>
                                    <span class="block text-sm text-slate-500 dark:text-slate-400">{{ __('Carte bancaire') }}</span>
                                </span>
                            </label>
                            @if($fedaPayAvailable)
                                <label class="mt-3 flex cursor-pointer items-start gap-3 rounded-xl border border-stone-200 p-4 dark:border-slate-700">
                                    <input type="radio" name="payment_method" value="fedapay" required @checked(old('payment_method') === 'fedapay') class="mt-1 accent-rose-600">
                                    <span>
                                        <span class="block font-bold text-slate-900 dark:text-white">FedaPay</span>
                                        <span class="block text-sm text-slate-500 dark:text-slate-400">{{ __('Mobile Money et autres moyens disponibles (XOF)') }}</span>
                                    </span>
                                </label>
                            @endif
                            @error('payment_method')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                        </fieldset>
                        <div class="sm:col-span-2">
                            <label for="email" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">{{ __('Adresse e-mail') }} <span class="text-rose-600">*</span></label>
                            <input id="email" name="email" type="email" required maxlength="255" autocomplete="email" value="{{ old('email', auth()->user()->email) }}" placeholder="vous@exemple.fr" class="min-h-12 w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-rose-500 focus:bg-white focus:ring-4 focus:ring-rose-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:bg-slate-900">
                            @error('email')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="first_name" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">{{ __('Prénom') }} <span class="text-rose-600">*</span></label>
                            <input id="first_name" name="first_name" type="text" required maxlength="255" autocomplete="given-name" value="{{ old('first_name', $defaultFirstName) }}" class="min-h-12 w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-rose-500 focus:bg-white focus:ring-4 focus:ring-rose-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:bg-slate-900">
                            @error('first_name')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="last_name" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">{{ __('Nom') }} <span class="text-rose-600">*</span></label>
                            <input id="last_name" name="last_name" type="text" required maxlength="255" autocomplete="family-name" value="{{ old('last_name', $defaultLastName) }}" class="min-h-12 w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-rose-500 focus:bg-white focus:ring-4 focus:ring-rose-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:bg-slate-900">
                            @error('last_name')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="phone" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">{{ __('Numéro de téléphone') }} <span class="text-rose-600">*</span></label>
                            <input id="phone" name="phone" type="tel" required maxlength="24" autocomplete="tel" inputmode="tel" value="{{ old('phone', auth()->user()->phone) }}" placeholder="+33 6 12 34 56 78" class="min-h-12 w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-rose-500 focus:bg-white focus:ring-4 focus:ring-rose-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:bg-slate-900">
                            <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">{{ __('Obligatoire pour le suivi de la livraison.') }}</p>
                            @error('phone')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="address" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">{{ __('Adresse') }} <span class="text-rose-600">*</span></label>
                            <input id="address" name="address" type="text" required maxlength="255" autocomplete="street-address" value="{{ old('address', auth()->user()->address) }}" placeholder="{{ __('Numéro et nom de rue') }}" class="min-h-12 w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-rose-500 focus:bg-white focus:ring-4 focus:ring-rose-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:bg-slate-900">
                            @error('address')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="postal_code" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">{{ __('Code postal') }} <span class="text-rose-600">*</span></label>
                            <input id="postal_code" name="postal_code" type="text" required maxlength="20" autocomplete="postal-code" value="{{ old('postal_code') }}" class="min-h-12 w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-rose-500 focus:bg-white focus:ring-4 focus:ring-rose-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:bg-slate-900">
                            @error('postal_code')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="city" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">{{ __('Ville') }} <span class="text-rose-600">*</span></label>
                            <input id="city" name="city" type="text" required maxlength="255" autocomplete="address-level2" value="{{ old('city') }}" class="min-h-12 w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-rose-500 focus:bg-white focus:ring-4 focus:ring-rose-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:bg-slate-900">
                            @error('city')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="country" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">{{ __('Pays') }} <span class="text-rose-600">*</span></label>
                            <input id="country" name="country" type="text" required maxlength="255" autocomplete="country-name" value="{{ old('country') }}" placeholder="{{ __('Votre pays') }}" class="min-h-12 w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-rose-500 focus:bg-white focus:ring-4 focus:ring-rose-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:bg-slate-900">
                            @error('country')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <p class="mt-7 border-t border-stone-100 pt-5 text-xs leading-5 text-slate-500 dark:border-slate-800 dark:text-slate-400">
                        {{ __('En continuant, vous serez redirigé vers le prestataire de paiement sélectionné.') }}
                    </p>
                </form>

                <aside class="space-y-5 lg:sticky lg:top-28">
                    <section class="storefront-form-card p-5 dark:bg-slate-900 sm:p-6">
                        <div class="mb-5 flex items-center justify-between gap-3">
                            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">{{ __('Votre commande') }}</h2>
                            <a href="{{ route('cart.index') }}" class="text-xs font-bold text-rose-600 transition hover:text-rose-700">{{ __('Modifier le panier') }}</a>
                        </div>

                        <div class="max-h-72 space-y-4 overflow-y-auto pr-1">
                            @foreach($cart as $item)
                                <div class="flex items-center gap-3">
                                    <div class="relative h-16 w-16 shrink-0 overflow-hidden rounded-xl border border-stone-100 bg-stone-50 dark:border-slate-700 dark:bg-slate-800">
                                        @if(!empty($item['image']))
                                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="h-full w-full object-cover">
                                        @else
                                            <span class="flex h-full w-full items-center justify-center text-slate-300" aria-label="{{ __('Image non disponible') }}">
                                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2 1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </span>
                                        @endif
                                        <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-slate-800 px-1 text-[10px] font-bold text-white">{{ $item['quantity'] }}</span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="line-clamp-2 text-sm font-bold leading-5 text-slate-800 dark:text-slate-100">{{ $item['name'] }}</p>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Quantité') }} : {{ $item['quantity'] }}</p>
                                    </div>
                                    <p class="shrink-0 text-sm font-bold text-slate-900 dark:text-white">@money($item['price'] * $item['quantity'])</p>
                                </div>
                            @endforeach
                        </div>

                        <div class="my-5 border-t border-dashed border-stone-200 dark:border-slate-700"></div>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between gap-3 text-slate-600 dark:text-slate-300">
                                <span>{{ __('Sous-total') }}</span>
                                <span class="font-semibold text-slate-900 dark:text-white">@money($subtotal)</span>
                            </div>
                            @if($coupon)
                                <div class="flex justify-between gap-3 text-emerald-700 dark:text-emerald-400">
                                    <span>{{ __('Réduction') }} <span class="font-bold">({{ $coupon->code }})</span></span>
                                    <span class="font-bold">−@money($discount)</span>
                                </div>
                            @endif
                            <div class="flex justify-between gap-3 border-t border-stone-100 pt-4 text-base dark:border-slate-700">
                                <span class="font-extrabold text-slate-900 dark:text-white">{{ __('Total à payer') }}</span>
                                <span class="font-extrabold text-slate-900 dark:text-white">@money($total)</span>
                            </div>
                        </div>

                        <button type="submit" form="checkout-form" class="storefront-primary-button mt-5 inline-flex min-h-13 w-full items-center justify-center gap-2 rounded-xl px-5 py-3 font-extrabold text-white shadow-lg shadow-rose-600/15 transition focus:outline-none focus:ring-4 focus:ring-rose-500/25">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2" stroke-width="1.8"/><path stroke-linecap="round" stroke-width="1.8" d="M3 10h18m-14 5h4"/></svg>
                            {{ __('Payer en toute sécurité') }}
                        </button>
                        <p class="mt-3 text-center text-xs text-slate-500 dark:text-slate-400">{{ __('Paiement chiffré et sécurisé par notre prestataire de paiement') }}</p>
                    </section>

                    <section class="storefront-form-card p-5 dark:bg-slate-900 sm:p-6">
                        <h2 class="mb-3 text-sm font-extrabold text-slate-900 dark:text-white">{{ __('Vous avez un code promo ?') }}</h2>
                        @if($coupon)
                            <div class="flex flex-wrap items-center justify-between gap-3 text-sm">
                                <p class="text-slate-600 dark:text-slate-300">{{ __('Code') }} <strong class="text-slate-900 dark:text-white">{{ $coupon->code }}</strong></p>
                                <form action="{{ route('cart.remove_coupon') }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-bold text-rose-600 hover:underline">{{ __('Retirer') }}</button>
                                </form>
                            </div>
                        @else
                            <form action="{{ route('cart.apply_coupon') }}" method="POST" class="flex gap-2">
                                @csrf
                                <label for="coupon_code" class="sr-only">{{ __('Saisissez votre code promo') }}</label>
                                <input id="coupon_code" type="text" name="coupon_code" value="{{ old('coupon_code') }}" required maxlength="50" placeholder="{{ __('Votre code') }}" class="min-h-11 min-w-0 flex-1 rounded-xl border border-stone-200 bg-stone-50 px-3 text-sm text-slate-900 outline-none focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                <button type="submit" class="min-h-11 rounded-xl border border-stone-200 px-4 text-sm font-bold text-slate-700 transition hover:border-rose-300 hover:text-rose-600 dark:border-slate-700 dark:text-slate-200">{{ __('Appliquer') }}</button>
                            </form>
                        @endif
                    </section>

                    <div class="flex items-center justify-center gap-2 px-4 text-xs text-slate-500 dark:text-slate-400">
                        <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3 5 6v5c0 4.7 3 8.3 7 10 4-1.7 7-5.3 7-10V6l-7-3Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 12 2 2 4-4"/></svg>
                        {{ __('Vos informations sont protégées.') }}
                    </div>
                </aside>
            </div>
        @endif
    </div>
@endsection
