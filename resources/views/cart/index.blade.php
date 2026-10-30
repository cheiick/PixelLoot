@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold mb-6">{{ __('Votre panier') }}</h2>
        @if(session('error'))
            <div class="mb-4 rounded border border-red-300 bg-red-50 px-4 py-3 text-red-700" role="alert">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="mb-4 rounded border border-green-300 bg-green-50 px-4 py-3 text-green-700" role="status">{{ session('success') }}</div>
        @endif
        @if(session()->has('cart') && count(session('cart')) > 0)
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Image') }}</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Produit') }}</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Prix unitaire') }}</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Quantité') }}</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Sous‑total') }}</th>
                        <th scope="col" class="px-6 py-3 text-center"></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php $total = 0; @endphp
                    @foreach(session('cart') as $productId => $item)
                        @php
                            $subtotal = $item['price'] * $item['quantity'];
                            $total += $subtotal;
                        @endphp
                        <tr data-cart-row data-unit-price="{{ $item['price'] }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item['image'])
                                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="h-16 w-16 object-cover rounded-md">
                                @else
                                    <div class="bg-gray-200 h-16 w-16 flex items-center justify-center rounded-md text-gray-500">Image</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $item['name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                @money($item['price'])
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="inline-flex items-center rounded-lg border border-gray-300" data-quantity-control>
                                    <button type="button" data-quantity-change="-1" data-url="{{ route('cart.update_quantity', $productId) }}" aria-label="{{ __('Diminuer la quantité de :product', ['product' => $item['name']]) }}" class="px-3 py-1 hover:bg-gray-100 rounded-l-lg">−</button>
                                    <span data-quantity-value class="min-w-10 px-2 text-center" data-stock="{{ $productStocks->get($productId, $item['quantity']) }}">{{ $item['quantity'] }}</span>
                                    <button type="button" data-quantity-change="1" data-url="{{ route('cart.update_quantity', $productId) }}" aria-label="{{ __('Augmenter la quantité de :product', ['product' => $item['name']]) }}" class="px-3 py-1 hover:bg-gray-100 rounded-r-lg">+</button>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <span id="subtotal-{{ $productId }}" data-cart-subtotal data-raw-amount="{{ $subtotal }}">@money($subtotal)</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <form action="{{ route('cart.remove', $productId) }}" method="post">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:text-red-800">{{ __('Supprimer') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-6 flex justify-between items-center">
    <a href="{{ route('catalog.index') }}" 
       class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 font-semibold">
        {{ __('Continuer les achats') }}
    </a>
    <a href="{{ route('checkout.show') }}" 
       class="inline-block bg-green-600 text-white px-8 py-3 rounded-lg hover:bg-green-700 font-semibold shadow-lg">
        {{ __('Passer la commande') }}
    </a>
</div>
        @else
            <p class="text-gray-600">{{ __('Votre panier est vide.') }}</p>
            <a href="{{ route('catalog.index') }}" class="inline-block bg-blue-600 text-white px-4 py-2 rounded mt-4 hover:bg-blue-700">{{ __('Faire des achats') }}</a>
        @endif
        @if(session()->has('cart') && count(session('cart')) > 0)
            <p class="mt-4 text-right text-xl font-bold">{{ __('Total :') }} <span data-cart-total data-raw-amount="{{ $total }}">@money($total)</span></p>
        @endif
    </div>
@endsection