@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-blue-700">Vue d’ensemble</p>
            <h1 class="mt-1 break-words text-2xl font-bold tracking-tight text-gray-950 sm:text-3xl">Dashboard administrateur</h1>
            <p class="mt-2 text-gray-600">Suivez l’activité de votre boutique et accédez rapidement à sa gestion.</p>
        </div>
        <a href="{{ route('home') }}" target="_blank" rel="noopener noreferrer" class="rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Voir la boutique ↗</a>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-2xl bg-slate-950 p-5 text-white shadow-sm">
            <p class="text-sm text-slate-300">Chiffre d’affaires total</p>
            <p class="mt-3 text-3xl font-bold">@money($totalRevenue)</p>
            <p class="mt-2 text-xs text-slate-400">Commandes payées, expédiées ou livrées</p>
        </article>
        <article class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Chiffre d’affaires ce mois</p>
            <p class="mt-3 text-3xl font-bold text-gray-950">@money($monthRevenue)</p>
            <p class="mt-2 text-xs text-gray-500">{{ now()->translatedFormat('F Y') }}</p>
        </article>
        <article class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Commandes</p>
            <p class="mt-3 text-3xl font-bold text-gray-950">{{ $orderCount }}</p>
            <a href="{{ route('admin.orders.index') }}" class="mt-2 inline-block text-sm font-semibold text-blue-700 hover:text-blue-900">{{ $pendingOrders }} en attente · Gérer →</a>
        </article>
        <article class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Catalogue & clients</p>
            <p class="mt-3 text-3xl font-bold text-gray-950">{{ $productCount }} <span class="text-base font-medium text-gray-500">produits</span></p>
            <p class="mt-2 text-sm text-gray-500">{{ $customerCount }} clients inscrits</p>
        </article>
    </div>

    <section>
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-950">Accès rapides</h2>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <a href="{{ route('admin.products.create') }}" class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="text-2xl">＋</span><h3 class="mt-3 font-semibold text-gray-900">Ajouter un produit</h3><p class="mt-1 text-sm text-gray-500">Compléter ou mettre à jour le catalogue.</p>
            </a>
            <a href="{{ route('admin.orders.index') }}" class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="text-2xl">▤</span><h3 class="mt-3 font-semibold text-gray-900">Gérer les commandes</h3><p class="mt-1 text-sm text-gray-500">{{ $pendingOrders }} commande(s) attendent une action.</p>
            </a>
            <a href="{{ route('admin.coupons.index') }}" class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="text-2xl">％</span><h3 class="mt-3 font-semibold text-gray-900">Codes promo</h3><p class="mt-1 text-sm text-gray-500">{{ $couponCount }} code(s) configuré(s).</p>
            </a>
            <a href="{{ route('admin.messages.index') }}" class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="text-2xl">✉</span><h3 class="mt-3 font-semibold text-gray-900">Messages clients</h3><p class="mt-1 text-sm text-gray-500">Consulter les demandes envoyées par le formulaire Contact.</p>
            </a>
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.4fr)_minmax(280px,0.8fr)]">
        <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4">
                <div><h2 class="font-bold text-gray-950">Commandes récentes</h2><p class="mt-1 text-sm text-gray-500">Les six dernières commandes de la boutique.</p></div>
                <a href="{{ route('admin.orders.index') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-900">Tout voir →</a>
            </div>
            @if($latestOrders->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500"><tr><th class="px-5 py-3">Commande</th><th class="px-5 py-3">Client</th><th class="px-5 py-3">Date</th><th class="px-5 py-3 text-right">Total</th><th class="px-5 py-3">Statut</th></tr></thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($latestOrders as $order)
                                <tr>
                                    <td class="px-5 py-3 font-semibold text-gray-900">#{{ $order->id }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $order->user?->name ?? 'Client supprimé' }}</td>
                                    <td class="whitespace-nowrap px-5 py-3 text-gray-500">{{ $order->created_at->format('d/m/Y') }}</td>
                                    <td class="whitespace-nowrap px-5 py-3 text-right font-medium">@money($order->total, $order->currency_code, $order->currency_symbol)</td>
                                    <td class="px-5 py-3"><span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">{{ $order->status }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="px-5 py-10 text-center text-sm text-gray-500">Aucune commande enregistrée pour le moment.</p>
            @endif
        </section>

        <div class="space-y-6">
            <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between"><div><h2 class="font-bold text-gray-950">Stock à surveiller</h2><p class="mt-1 text-sm text-gray-500">Produits avec moins de 5 unités.</p></div><a href="{{ route('admin.products.index') }}" class="text-sm font-semibold text-blue-700">Produits →</a></div>
                @forelse($lowStockProducts as $product)
                    <div class="mt-4 flex items-center justify-between gap-3 border-t border-gray-100 pt-3">
                        <span class="truncate text-sm font-medium text-gray-800">{{ $product->name }}</span>
                        <span class="shrink-0 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800">{{ $product->stock }} restant(s)</span>
                    </div>
                @empty
                    <p class="mt-4 text-sm text-emerald-700">Tous les produits ont un stock suffisant.</p>
                @endforelse
            </section>

            <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <h2 class="font-bold text-gray-950">Meilleures ventes</h2>
                @forelse($topProducts as $topProduct)
                    <div class="mt-4 flex items-center justify-between gap-3 border-t border-gray-100 pt-3">
                        <span class="truncate text-sm font-medium text-gray-800">{{ $topProduct->product?->name ?? 'Produit supprimé' }}</span>
                        <span class="shrink-0 text-sm text-gray-500">{{ $topProduct->quantity_sold }} vendu(s)</span>
                    </div>
                @empty
                    <p class="mt-3 text-sm text-gray-500">Les ventes apparaîtront ici après les premières commandes.</p>
                @endforelse
            </section>
        </div>
    </div>
</div>
@endsection
