@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">
    <h1 class="text-2xl font-bold mb-6">Modifier le produit</h1>

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium mb-1">Nom</label>
            <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Marque</label>
            <input type="text" name="brand" value="{{ old('brand', $product->brand) }}" maxlength="150" class="w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea name="description" rows="3" class="w-full border rounded px-3 py-2">{{ old('description', $product->description) }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Prix') }} ({{ app(\App\Support\CurrencyFormatter::class)->symbol() }})</label>
                <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" required class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Stock</label>
                <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" required class="w-full border rounded px-3 py-2">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Image actuelle</label>
            @if($product->image_path)
                <img src="{{ \App\Support\ImageStorage::url($product->image_path) }}" alt="{{ $product->name }}" class="h-32 mb-2">
            @elseif($product->image_url)
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-32 mb-2">
            @else
                <p class="text-gray-500">Aucune image</p>
            @endif
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Nouvelle image depuis le PC (10 Mo maximum, facultatif)</label>
            <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="w-full border rounded px-3 py-2">
            <p class="text-xs text-gray-500 mt-1">Laisse vide pour garder l'image actuelle</p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">URL Image (optionnel, si pas de fichier)</label>
            <input type="text" name="image_url" value="{{ old('image_url', $product->image_url) }}" class="w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Catégorie</label>
            <select name="category_id" required class="w-full border rounded px-3 py-2">
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-2">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $product->is_active))>
            <label for="is_active">Actif</label>
        </div>

        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">Mettre à jour</button>
    </form>
</div>
@endsection