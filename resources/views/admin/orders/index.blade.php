@extends('layouts.admin')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Gestion des commandes</h1>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N°</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Articles achetés</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Changer le statut</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($orders as $order)
                        <tr>
                            <td class="px-6 py-4 font-medium">#{{ $order->id }}</td>
                            <td class="px-6 py-4">{{ $order->user->email ?? '—' }}</td>
                            <td class="px-6 py-4">
                                @forelse($order->items as $item)
                                    <div class="mb-1 last:mb-0">
                                        {{ $item->quantity }} × {{ $item->product->name ?? 'Produit supprimé' }}
                                        <span class="text-gray-500">(@money($item->price * $item->quantity, $order->currency_code, $order->currency_symbol))</span>
                                    </div>
                                @empty
                                    <span class="text-gray-500">Aucun article</span>
                                @endforelse
                            </td>
                            <td class="px-6 py-4">@money($order->total, $order->currency_code, $order->currency_symbol)</td>
                            <td class="px-6 py-4">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $couleurs = [
                                        'pending'   => 'bg-red-100 text-red-800',
                                        'paid'      => 'bg-green-100 text-green-800',
                                        'shipped'   => 'bg-blue-100 text-blue-800',
                                        'delivered' => 'bg-gray-100 text-gray-800',
                                    ];
                                @endphp
                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $couleurs[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $order->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
    <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST" class="flex items-center gap-1">
        @csrf
        <button type="submit" name="status" value="pending"
            class="px-2 py-1 text-xs rounded {{ $order->status === 'pending' ? 'bg-red-600 text-white' : 'bg-red-100 text-red-800 hover:bg-red-200' }}">
            En attente
        </button>
        <button type="submit" name="status" value="paid"
            class="px-2 py-1 text-xs rounded {{ $order->status === 'paid' ? 'bg-green-600 text-white' : 'bg-green-100 text-green-800 hover:bg-green-200' }}">
            Payée
        </button>
        <button type="submit" name="status" value="shipped"
            class="px-2 py-1 text-xs rounded {{ $order->status === 'shipped' ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-800 hover:bg-blue-200' }}">
            Expédiée
        </button>
        <button type="submit" name="status" value="delivered"
            class="px-2 py-1 text-xs rounded {{ $order->status === 'delivered' ? 'bg-gray-600 text-white' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' }}">
            Livrée
        </button>
    </form>
</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                Aucune commande pour le moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection