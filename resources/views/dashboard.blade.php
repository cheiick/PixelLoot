@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-blue-700">Espace client</p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight text-gray-950">Bonjour {{ $user->name }}</h1>
                <p class="mt-2 text-gray-600">Gérez vos informations et retrouvez vos commandes.</p>
            </div>
            <a href="{{ route('catalog.index') }}" class="inline-flex items-center rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white shadow-sm transition hover:bg-blue-700">
                Continuer mes achats
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800" role="status">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800" role="alert">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
        @if(!$user->email)
            <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-900">
                <p class="font-semibold">Ajoutez une adresse e-mail à votre compte</p>
                <p class="mt-1 text-sm">Elle facilitera la récupération de votre compte et le suivi de vos commandes.</p>
            </div>
        @endif

        <div class="grid gap-8 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
            <section class="h-fit rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-gray-950">Mes informations</h2>
                    <p class="mt-1 text-sm text-gray-500">Mettez à jour vos coordonnées et votre adresse de livraison.</p>
                </div>

                <form action="{{ route('dashboard.update') }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700">Nom complet</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required maxlength="255" autocomplete="name" class="w-full rounded-xl border border-gray-300 px-4 py-3 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700">Adresse e-mail</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" maxlength="255" autocomplete="email" placeholder="vous@exemple.fr" class="w-full rounded-xl border border-gray-300 px-4 py-3 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="phone" class="mb-1.5 block text-sm font-medium text-gray-700">Numéro de téléphone</label>
                        <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" autocomplete="tel" placeholder="+33 6 12 34 56 78" class="w-full rounded-xl border border-gray-300 px-4 py-3 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <p class="mt-1 text-xs text-gray-500">Au moins une adresse e-mail ou un numéro de téléphone doit être renseigné.</p>
                        @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="address" class="mb-1.5 block text-sm font-medium text-gray-700">Adresse de livraison</label>
                        <textarea id="address" name="address" rows="3" maxlength="2000" autocomplete="street-address" placeholder="Adresse, code postal, ville" class="w-full rounded-xl border border-gray-300 px-4 py-3 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">{{ old('address', $user->address) }}</textarea>
                        @error('address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" class="w-full rounded-xl bg-gray-950 px-5 py-3 font-semibold text-white transition hover:bg-blue-700 sm:w-auto">Enregistrer mes informations</button>
                </form>
            </section>

            <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
                <div class="mb-6 flex items-end justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-bold text-gray-950">Mes commandes</h2>
                        <p class="mt-1 text-sm text-gray-500">Consultez l’historique de vos achats.</p>
                    </div>
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-sm font-semibold text-blue-700">{{ $orders->total() }}</span>
                </div>

                @forelse($orders as $order)
                    <article class="mb-4 rounded-xl border border-gray-200 p-4 last:mb-0">
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-gray-100 pb-3">
                            <div>
                                <h3 class="font-semibold text-gray-900">Commande #{{ $order->id }}</h3>
                                <p class="mt-1 text-sm text-gray-500">{{ $order->created_at->format('d/m/Y à H:i') }}</p>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $order->status === 'paid' ? 'bg-emerald-50 text-emerald-700' : ($order->status === 'cancelled' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-800') }}">
                                {{ match($order->status) { 'paid' => 'Payée', 'cancelled' => 'Annulée', default => 'En attente' } }}
                            </span>
                        </div>
                        <ul class="space-y-2 py-3 text-sm text-gray-600">
                            @foreach($order->items as $item)
                                <li class="flex justify-between gap-3">
                                    <span>{{ $item->product?->name ?? 'Produit supprimé' }} × {{ $item->quantity }}</span>
                                    <span class="whitespace-nowrap">@money($item->price * $item->quantity, $order->currency_code, $order->currency_symbol)</span>
                                </li>
                            @endforeach
                        </ul>
                        <div class="flex justify-between border-t border-gray-100 pt-3 font-semibold text-gray-950">
                            <span>Total</span>
                            <span>@money($order->total, $order->currency_code, $order->currency_symbol)</span>
                        </div>
                    </article>
                @empty
                    <div class="rounded-xl bg-gray-50 px-5 py-10 text-center">
                        <p class="font-medium text-gray-800">Vous n’avez pas encore de commande.</p>
                        <p class="mt-1 text-sm text-gray-500">Vos prochains achats apparaîtront ici.</p>
                        <a href="{{ route('catalog.index') }}" class="mt-4 inline-flex font-semibold text-blue-700 hover:text-blue-900">Découvrir le catalogue →</a>
                    </div>
                @endforelse

                @if($orders->hasPages())
                    <div class="mt-5">{{ $orders->links() }}</div>
                @endif
            </section>
        </div>
    </div>
@endsection
