@if($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<form action="{{ $action }}" method="POST" class="max-w-2xl bg-white p-6 rounded shadow space-y-4">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div>
        <label for="name" class="block text-sm font-medium mb-1">Nom</label>
        <input id="name" name="name" type="text" value="{{ old('name', $category?->name) }}" required maxlength="255" class="w-full border rounded px-3 py-2">
    </div>

    <div>
        <label for="slug" class="block text-sm font-medium mb-1">Slug généré automatiquement</label>
        <input id="slug" type="text" value="{{ old('slug', $category?->slug) }}" readonly class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-600">
        <p class="text-sm text-gray-500 mt-1">Le slug est créé à partir du nom de la catégorie.</p>
    </div>

    <div>
        <label for="description" class="block text-sm font-medium mb-1">Description (facultative)</label>
        <textarea id="description" name="description" rows="4" class="w-full border rounded px-3 py-2">{{ old('description', $category?->description) }}</textarea>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700">{{ $buttonLabel }}</button>
        <a href="{{ route('admin.categories.index') }}" class="border px-5 py-2 rounded hover:bg-gray-50">Annuler</a>
    </div>
</form>

<script>
    const categoryName = document.getElementById('name');
    const categorySlug = document.getElementById('slug');

    const updateCategorySlug = () => {
        categorySlug.value = categoryName.value
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    };

    categoryName.addEventListener('input', updateCategorySlug);
    if (!categorySlug.value) {
        updateCategorySlug();
    }
</script>
