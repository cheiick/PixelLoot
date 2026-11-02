@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
        <p class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-rose-600">{{ __('Dernière étape') }}</p>
        <h1 class="mb-7 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">{{ __('Passer la commande') }}</h1>

        @if($errors->any())
            <div class="mb-6 rounded border border-red-300 bg-red-50 px-4 py-3 text-red-800">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @if(session('coupon_success'))
            <div class="mb-6 rounded border border-green-300 bg-green-50 px-4 py-3 text-green-800">
                {{ session('coupon_success') }}
            </div>
        @endif

        @if(session('coupon_error'))
            <div class="mb-6 rounded border border-red-300 bg-red-50 px-4 py-3 text-red-800">
                {{ session('coupon_error') }}
            </div>
        @endif

        @if(empty($cart))
            <p class="mb-4">{{ __('Votre panier est vide.') }}</p>
            <a href="{{ route('home') }}" class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">{{ __('Retour aux produits') }}</a>
        @else
            <section class="storefront-form-card mb-8 p-5 sm:p-6">
                <h2 class="mb-4 text-lg font-bold">{{ __('Code promo') }}</h2>
                @if($coupon)
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <p>{{ __('Code') }} <strong>{{ $coupon->code }}</strong> {{ __('appliqué.') }}</p>
                        <form action="{{ route('cart.remove_coupon') }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="font-semibold text-rose-600 hover:underline">{{ __('Retirer le code promo') }}</button>
                        </form>
                    </div>
                @else
                    <form action="{{ route('cart.apply_coupon') }}" method="POST" class="flex flex-wrap items-end gap-3">
                        @csrf
                        <div class="min-w-0 flex-1">
                            <label for="coupon_code" class="block text-sm font-medium mb-1">{{ __('Saisissez votre code promo') }}</label>
                            <input type="text" name="coupon_code" id="coupon_code" value="{{ old('coupon_code') }}" required maxlength="255" class="w-full border rounded px-3 py-2">
                        </div>
                        <button type="submit" class="storefront-primary-button rounded-lg px-5 py-2.5 font-semibold text-white">{{ __('Appliquer') }}</button>
                    </form>
                @endif
            </section>

            <form action="{{ route('checkout.store') }}" method="POST" class="storefront-form-card p-4 sm:p-6">
                @csrf
                <div class="mb-6">
                    <label for="address" class="block text-sm font-medium mb-2">{{ __('Adresse de livraison') }}</label>
                    <textarea id="address" name="address" rows="3" required maxlength="2000" class="w-full border rounded p-2">{{ old('address', auth()->user()->address) }}</textarea>
                </div>

                <h2 class="mb-4 text-xl font-bold">{{ __('Récapitulatif du panier') }}</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 mb-6">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">{{ __('Produit') }}</th>
                                <th class="px-4 py-3 text-right text-sm font-medium text-gray-500">{{ __('Quantité') }}</th>
                                <th class="px-4 py-3 text-right text-sm font-medium text-gray-500">{{ __('Prix unitaire') }}</th>
                                <th class="px-4 py-3 text-right text-sm font-medium text-gray-500">{{ __('Sous-total') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($cart as $item)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="flex min-w-56 items-center gap-3">
                                            @if(!empty($item['image']))
                                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="h-14 w-14 shrink-0 rounded-lg border border-gray-100 object-cover">
                                            @else
                                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-400" aria-label="{{ __('Image non disponible') }}">
                                                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                </div>
                                            @endif
                                            <span class="font-medium text-gray-900">{{ $item['name'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right">{{ $item['quantity'] }}</td>
                                    <td class="px-4 py-3 text-right">@money($item['price'])</td>
                                    <td class="px-4 py-3 text-right font-medium">@money($item['price'] * $item['quantity'])</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="px-4 py-2 text-right">{{ __('Sous-total :') }}</td>
                                <td class="px-4 py-2 text-right">@money($subtotal)</td>
                            </tr>
                            @if($coupon)
                                <tr>
                                    <td colspan="3" class="px-4 py-2 text-right">{{ __('Réduction') }} ({{ $coupon->code }}) :</td>
                                    <td class="px-4 py-2 text-right text-green-700">−@money($discount)</td>
                                </tr>
                            @endif
                            <tr>
                                <td colspan="3" class="px-4 py-2 text-right font-semibold">{{ __('Total à payer :') }}</td>
                                <td class="px-4 py-2 text-right font-semibold">@money($total)</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <button type="submit" class="storefront-primary-button min-h-12 w-full rounded-lg px-6 py-3 font-bold text-white sm:w-auto">
                    {{ __('Procéder au paiement') }}
                </button>
            </form>
        @endif
    </div>
@endsection
