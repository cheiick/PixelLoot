@extends('layouts.app')
@section('meta_robots', 'noindex,nofollow')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 dark:bg-slate-950">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <h1 class="mb-8 text-3xl font-bold text-gray-900 dark:text-white">{{ __('Ma liste de souhaits') }}</h1>
        @if(session('success'))
            <p class="mb-5 rounded-lg bg-green-50 p-3 text-green-700">{{ session('success') }}</p>
        @endif
        @if($products->isNotEmpty())
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($products as $product)
                    <article class="overflow-hidden rounded-xl bg-white shadow dark:bg-slate-900">
                        <a href="{{ route('products.show', $product) }}">
                            @if($product->image_path)
                                <img src="{{ \App\Support\ImageStorage::url($product->image_path) }}" alt="{{ $product->name }}" class="aspect-square w-full object-cover">
                            @elseif($product->image_url)
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="aspect-square w-full object-cover">
                            @else
                                <div class="flex aspect-square items-center justify-center bg-gray-100 text-gray-500">Image indisponible</div>
                            @endif
                        </a>
                        <div class="p-4">
                            <h2 class="font-semibold text-gray-900 dark:text-white">{{ $product->name }}</h2>
                            <p class="my-2 text-lg font-bold text-gray-900 dark:text-white">@money($product->price)</p>
                            <form action="{{ route('wishlist.destroy', $product) }}" method="POST">
                                @csrf @method('DELETE')
                                <button class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-slate-700 dark:text-gray-200 dark:hover:bg-slate-800">{{ __('Retirer') }}</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="mt-8">{{ $products->links() }}</div>
        @else
            <div class="rounded-xl bg-white p-8 text-center shadow dark:bg-slate-900">
                <p class="text-gray-600 dark:text-gray-300">{{ __('Votre liste de souhaits est vide.') }}</p>
                <a href="{{ route('catalog.index') }}" class="mt-4 inline-block font-semibold text-blue-600 hover:underline">{{ __('Découvrir le catalogue') }}</a>
            </div>
        @endif
    </div>
</div>
@endsection
