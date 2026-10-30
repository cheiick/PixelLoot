<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('name')->paginate(10);

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);
        $validated['slug'] = $this->uniqueSlug($validated['name']);

        Category::create($validated);
        Cache::forget('storefront.home.categories');
        Cache::forget('storefront.catalog.facets');

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Catégorie créée avec succès.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($validated['name'] !== $category->name) {
            $validated['slug'] = $this->uniqueSlug($validated['name'], $category);
        }

        $category->update($validated);
        Cache::forget('storefront.home.categories');
        Cache::forget('storefront.catalog.facets');

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Catégorie mise à jour avec succès.');
    }

    private function uniqueSlug(string $name, ?Category $category = null): string
    {
        $baseSlug = Str::slug($name) ?: 'categorie';
        $slug = $baseSlug;
        $suffix = 2;

        $query = Category::where('slug', $slug);

        if ($category) {
            $query->where('id', '!=', $category->id);
        }

        while ($query->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
            $query = Category::where('slug', $slug);

            if ($category) {
                $query->where('id', '!=', $category->id);
            }
        }

        return $slug;
    }
}
