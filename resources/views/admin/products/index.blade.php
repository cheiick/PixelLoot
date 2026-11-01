@extends('layouts.admin')

@section('title', 'Gestion des produits')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold">Liste des produits</h1>
        <a href="{{ route('admin.products.create') }}" class="rounded bg-blue-600 px-4 py-2 text-center text-white hover:bg-blue-700">Ajouter produit</a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto">
    <table class="min-w-[50rem] border-collapse border">
        <thead>
            <tr class="bg-gray-200">
                <th class="border px-4 py-2">ID</th>
                <th class="border px-4 py-2">Image</th>
                <th class="border px-4 py-2">Nom</th>
                <th class="border px-4 py-2">{{ __('Prix') }} ({{ app(\App\Support\CurrencyFormatter::class)->symbol() }})</th>
                <th class="border px-4 py-2">Stock</th>
                <th class="border px-4 py-2">Actif</th>
                <th class="border px-4 py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr class="hover:bg-gray-50">
                    <td class="border px-4 py-2">{{ $product->id }}</td>
                    <td class="border px-4 py-2">
                        @if($product->image_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image_path) }}" alt="{{ $product->name }}" loading="lazy" class="h-12 w-12 rounded object-cover">
                        @elseif($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy" class="h-12 w-12 rounded object-cover">
                        @else
                            <span class="text-gray-500">Aucune image</span>
                        @endif
                    </td>
                    <td class="border px-4 py-2">{{ $product->name }}</td>
                    <td class="border px-4 py-2">@money($product->price)</td>
                    <td class="border px-4 py-2">{{ $product->stock }}</td>
                    <td class="border px-4 py-2">
                        @if($product->is_active)
                            Oui
                        @else
                            Non
                        @endif
                    </td>
                    <td class="border px-4 py-2 space-x-2">
                        <a href="{{ route('admin.products.edit', $product) }}" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">Modifier</a>
                        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Êtes‑vous sûr de vouloir supprimer ce produit ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">Supprimer</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-4">Aucun produit trouvé.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    </div>

    <div class="mt-4">
        {{ $products->links() }}
    </div>
@endsection
