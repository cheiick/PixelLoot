@extends('layouts.admin')

@section('title', 'Gestion des catégories')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">Catégories de produits</h1>
        <a href="{{ route('admin.categories.create') }}" class="rounded bg-blue-600 px-4 py-2 text-center text-white hover:bg-blue-700">
            Ajouter une catégorie
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-lg bg-white shadow">
      <div class="overflow-x-auto">
        <table class="min-w-[48rem] divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slug</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produits</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($categories as $category)
                    <tr>
                        <td class="px-6 py-4 font-medium">{{ $category->name }}</td>
                        <td class="px-6 py-4">{{ $category->slug }}</td>
                        <td class="px-6 py-4">{{ $category->products_count }}</td>
                        <td class="px-6 py-4">{{ $category->description ?: '—' }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="text-blue-600 hover:underline">
                                Modifier
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">Aucune catégorie enregistrée.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>

    <div class="mt-4">{{ $categories->links() }}</div>
@endsection
