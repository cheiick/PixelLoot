@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 sm:py-10 lg:px-8">
        <h1 class="mb-6 text-2xl font-bold text-gray-900 dark:text-white sm:text-3xl">{{ __('Votre panier') }}</h1>

        @if(session('error'))
            <div class="mb-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-red-700" role="alert">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="mb-4 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-green-700" role="status">{{ session('success') }}</div>
        @endif

        @if(session()->has('cart') && count(session('cart')) > 0)
            @php $total = 0; @endphp
            <div class="space-y-4">
                @foreach(session('cart') as $productId => $item)
                    @php
                        $subtotal = $item['price'] * $item['quantity'];
                        $total += $subtotal;
                    @endphp
                    <article data-cart-row data-unit-price="{{ $item['price'] }}" class="grid min-w-0 grid-cols-[4rem_minmax(0,1fr)] gap-3 rounded-2xl border border-gray-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:grid-cols-[6rem_minmax(0,1fr)] sm:gap-5 sm:p-5">
                        <div class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-xl bg-gray-100 text-xs text-gray-500 dark:bg-slate-800 sm:h-24 sm:w-24">
                            @if($item['image'])
                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="h-full w-full object-cover">
                            @else
                                <span>{{ __('Image') }}</span>
                            @endif
                        </div>

                        <div class="min-w-0">
                            <h2 class="break-words pr-1 text-base font-semibold leading-6 text-gray-900 dark:text-white sm:text-lg">{{ $item['name'] }}</h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ __('Prix unitaire') }} : @money($item['price'])</p>

                            <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                                <div class="inline-flex h-10 items-center rounded-lg border border-gray-300 dark:border-slate-700" data-quantity-control>
                                    <button type="button" data-quantity-change="-1" data-url="{{ route('cart.update_quantity', $productId) }}" aria-label="{{ __('Diminuer la quantité de :product', ['product' => $item['name']]) }}" class="h-10 w-10 rounded-l-lg text-lg hover:bg-gray-100 disabled:opacity-50 dark:hover:bg-slate-800">−</button>
                                    <span data-quantity-value class="min-w-10 px-2 text-center font-medium text-gray-900 dark:text-white" data-stock="{{ $productStocks->get($productId, $item['quantity']) }}">{{ $item['quantity'] }}</span>
                                    <button type="button" data-quantity-change="1" data-url="{{ route('cart.update_quantity', $productId) }}" aria-label="{{ __('Augmenter la quantité de :product', ['product' => $item['name']]) }}" class="h-10 w-10 rounded-r-lg text-lg hover:bg-gray-100 disabled:opacity-50 dark:hover:bg-slate-800">+</button>
                                </div>

                                <p class="text-right text-sm font-semibold text-gray-900 dark:text-white">
                                    <span class="mr-1 text-xs font-normal text-gray-500">{{ __('Sous-total') }}</span>
                                    <span data-cart-subtotal data-raw-amount="{{ $subtotal }}">@money($subtotal)</span>
                                </p>
                            </div>

                            <form action="{{ route('cart.remove', $productId) }}" method="post" class="mt-3 text-right">
                                @csrf
                                <button type="submit" class="min-h-10 rounded-lg px-3 text-sm font-medium text-red-600 hover:bg-red-50 hover:text-red-800 dark:hover:bg-red-950/40">{{ __('Supprimer') }}</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
                <p class="text-right text-lg font-bold text-gray-900 dark:text-white sm:text-xl">
                    {{ __('Total :') }}
                    <span data-cart-total data-raw-amount="{{ $total }}">@money($total)</span>
                </p>
                <div class="mt-5 grid gap-3 sm:flex sm:justify-end">
                    <a href="{{ route('catalog.index') }}" class="inline-flex min-h-12 items-center justify-center rounded-lg border border-gray-300 px-5 py-3 text-center font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-slate-700 dark:text-gray-100 dark:hover:bg-slate-800">
                        {{ __('Continuer les achats') }}
                    </a>
                    <a href="{{ route('checkout.show') }}" class="inline-flex min-h-12 items-center justify-center rounded-lg bg-green-600 px-6 py-3 text-center font-semibold text-white shadow transition hover:bg-green-700">
                        {{ __('Passer la commande') }}
                    </a>
                </div>
            </div>
        @else
            <div class="rounded-2xl border border-gray-200 bg-white p-6 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-10">
                <p class="text-gray-600 dark:text-gray-300">{{ __('Votre panier est vide.') }}</p>
                <a href="{{ route('catalog.index') }}" class="mt-4 inline-flex min-h-11 items-center justify-center rounded-lg bg-blue-600 px-5 py-2 font-semibold text-white transition hover:bg-blue-700">{{ __('Faire des achats') }}</a>
            </div>
        @endif
    </div>
@endsection
