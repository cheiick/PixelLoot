<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\Category;
use App\Support\UploadedImageOptimizer;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index()
    {
        $products = Product::with('category')->paginate(10);
        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:150',
            'description' => 'nullable|string|max:10000',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image_url' => ['nullable', 'url:http,https', 'max:2048'],
            'category_id' => 'required|exists:categories,id',
            'is_active' => 'sometimes|boolean',
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240', 'dimensions:max_width=10000,max_height=10000'],
        ]);

        if ($request->hasFile('image')) {
            $path = app(UploadedImageOptimizer::class)->storeWebp($request->file('image'), 'products');
            $data['image_path'] = $path;
        }

        Product::create($data);
        Cache::forget('storefront.catalog.facets');

        return redirect()->route('admin.products.index')->with('success', 'Produit créé avec succès.');
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:150',
            'description' => 'nullable|string|max:10000',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image_url' => ['nullable', 'url:http,https', 'max:2048'],
            'category_id' => 'required|exists:categories,id',
            'is_active' => 'sometimes|boolean',
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240', 'dimensions:max_width=10000,max_height=10000'],
        ]);

        $previousImagePath = null;
        if ($request->hasFile('image')) {
            $previousImagePath = $product->image_path;
            $path = app(UploadedImageOptimizer::class)->storeWebp($request->file('image'), 'products');
            $data['image_path'] = $path;
        }

        $product->update($data);
        if ($previousImagePath) {
            Storage::disk('public')->delete($previousImagePath);
        }
        Cache::forget('storefront.catalog.facets');

        return redirect()->route('admin.products.index')->with('success', 'Produit mis à jour avec succès.');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        Cache::forget('storefront.catalog.facets');
        return redirect()->route('admin.products.index')->with('success', 'Produit supprimé avec succès.');
    }
}
