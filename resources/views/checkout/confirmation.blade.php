@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold mb-6">{{ __('Merci pour votre commande') }}</h1>
        <p class="mb-4">{{ __('Votre commande a bien été enregistrée. Vous recevrez un e‑mail de confirmation sous peu.') }}</p>

        <div class="bg-white shadow rounded p-6 mb-6">
            <h2 class="text-xl font-semibold mb-3">{{ __('Adresse de livraison') }}</h2>
            <p>{{ $order->address }}</p>
        </div>

        <div class="bg-white shadow rounded p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">{{ __('Récapitulatif des articles') }}</h2>
            <table class="min-w-full divide-y divide-gray-200 mb-4">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">{{ __('Produit') }}</th>
                        <th class="px-4 py-2 text-right text-sm font-medium text-gray-500">{{ __('Quantité') }}</th>
                        <th class="px-4 py-2 text-right text-sm font-medium text-gray-500">{{ __('Prix unitaire') }}</th>
                        <th class="px-4 py-2 text-right text-sm font-medium text-gray-500">{{ __('Sous‑total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php $grandTotal = 0; @endphp
                    @foreach($order->items as $item)
                        @php
                            $subtotal = $item->price * $item->quantity;
                            $grandTotal += $subtotal;
                        @endphp
                        <tr>
                            <td class="px-4 py-2">{{ $item->product->name }}</td>
                            <td class="px-4 py-2 text-right">{{ $item->quantity }}</td>
                            <td class="px-4 py-2 text-right">@money($item->price, $order->currency_code, $order->currency_symbol)</td>
                            <td class="px-4 py-2 text-right">@money($subtotal, $order->currency_code, $order->currency_symbol)</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="3" class="px-4 py-2 text-right">{{ __('Sous-total :') }}</td>
                        <td class="px-4 py-2 text-right">@money($grandTotal, $order->currency_code, $order->currency_symbol)</td>
                    </tr>
                    @if($order->discount_amount > 0)
                        <tr>
                            <td colspan="3" class="px-4 py-2 text-right">
                                {{ __('Réduction') }}{{ $order->coupon_code ? ' ('.$order->coupon_code.')' : '' }} :
                            </td>
                            <td class="px-4 py-2 text-right text-green-700">−@money($order->discount_amount, $order->currency_code, $order->currency_symbol)</td>
                        </tr>
                    @endif
                    <tr>
                        <td colspan="3" class="px-4 py-2 font-semibold text-right">{{ __('Total payé :') }}</td>
                        <td class="px-4 py-2 font-semibold text-right">@money($order->total, $order->currency_code, $order->currency_symbol)</td>
                    </tr>
                </tbody>
            </table>
            <p class="text-right font-medium">{{ __('Statut :') }} {{ ucfirst($order->status) }}</p>
        </div>

        <a href="{{ route('home') }}" class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">{{ __('Retour aux produits') }}</a>
    </div>
@endsection