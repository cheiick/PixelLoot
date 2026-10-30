@extends('layouts.app')
@section('title', 'Catalogue - '.\App\Models\SiteSetting::getValue('site_name', 'MonShop'))
@section('meta_description', 'Parcourez notre catalogue de produits gaming et high-tech. Filtrez par marque, catégorie, prix et disponibilité.')

@section('content')
<div class="min-h-screen bg-gray-50 py-8 dark:bg-slate-950">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('Tous nos produits') }}</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-300"><span data-result-count>{{ $products->total() }}</span> {{ __('produits disponibles') }}</p>
        </div>

        <div class="flex flex-col gap-8 lg:flex-row">
            <aside class="h-fit flex-shrink-0 rounded-xl bg-white p-6 shadow dark:bg-slate-900 lg:sticky lg:top-24 lg:w-64">
                <form method="GET" action="{{ route('catalog.index') }}" data-catalog-form class="space-y-4">
                    <div>
                        <label for="catalog-search" class="mb-2 block text-sm font-semibold text-gray-900 dark:text-white">{{ __('Rechercher') }}</label>
                        <input id="catalog-search" type="search" name="search" value="{{ request('search') }}" placeholder="{{ __('Nom du produit...') }}"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    </div>
                    <div>
                        <label for="catalog-category" class="mb-2 block text-sm font-semibold text-gray-900 dark:text-white">{{ __('Catégorie') }}</label>
                        <select id="catalog-category" name="category" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            <option value="">{{ __('Toutes les catégories') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="catalog-brand" class="mb-2 block text-sm font-semibold text-gray-900 dark:text-white">{{ __('Marque') }}</label>
                        <select id="catalog-brand" name="brand" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            <option value="">{{ __('Toutes les marques') }}</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand }}" @selected(request('brand') === $brand)>{{ $brand }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-900 dark:text-white">{{ __('Prix') }} ({{ app(\App\Support\CurrencyFormatter::class)->symbol() }})</label>
                        <div class="flex gap-2">
                            <input type="number" min="0" step="0.01" name="min_price" value="{{ request('min_price') }}" placeholder="Min" aria-label="{{ __('Prix minimum') }}"
                                class="w-1/2 rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            <input type="number" min="0" step="0.01" name="max_price" value="{{ request('max_price') }}" placeholder="Max" aria-label="{{ __('Prix maximum') }}"
                                class="w-1/2 rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        </div>
                    </div>
                    <div>
                        <label for="catalog-sort" class="mb-2 block text-sm font-semibold text-gray-900 dark:text-white">{{ __('Trier par') }}</label>
                        <select id="catalog-sort" name="sort" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            <option value="newest" @selected(request('sort', 'newest') === 'newest')>{{ __('Nouveautés') }}</option>
                            <option value="price_asc" @selected(request('sort') === 'price_asc')>{{ __('Prix croissant') }}</option>
                            <option value="price_desc" @selected(request('sort') === 'price_desc')>{{ __('Prix décroissant') }}</option>
                            <option value="name_asc" @selected(request('sort') === 'name_asc')>{{ __('Nom A-Z') }}</option>
                            <option value="name_desc" @selected(request('sort') === 'name_desc')>{{ __('Nom Z-A') }}</option>
                        </select>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                        <input type="checkbox" name="in_stock" value="1" @checked(request('in_stock') === '1') class="rounded text-blue-600">
                        {{ __('En stock uniquement') }}
                    </label>
                    <button type="submit" class="w-full rounded-lg bg-blue-600 py-2 font-semibold text-white transition hover:bg-blue-700">{{ __('Appliquer les filtres') }}</button>
                    <a href="{{ route('catalog.index') }}" class="block text-center text-sm text-blue-600 hover:underline">{{ __('Réinitialiser') }}</a>
                </form>
            </aside>

            <section class="min-w-0 flex-1">
                <p class="mb-4 text-sm text-gray-600 dark:text-gray-300" aria-live="polite">
                    {{ __('Affichage de') }} <span data-result-first>{{ $products->firstItem() ?? 0 }}</span> à
                    <span data-result-last>{{ $products->lastItem() ?? 0 }}</span> {{ __('sur') }}
                    <span data-result-total>{{ $products->total() }}</span> {{ __('produits disponibles') }}
                </p>
                <div data-catalog-results aria-live="polite">
                    @include('catalog.partials.products', ['products' => $products])
                </div>
            </section>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const form = document.querySelector('[data-catalog-form]');
    const results = document.querySelector('[data-catalog-results]');
    if (!form || !results) return;

    let timer;
    let activeRequest;

    const search = async (url = null, updateHistory = true) => {
        if (activeRequest) activeRequest.abort();
        activeRequest = new AbortController();
        const target = url || `${form.action}?${new URLSearchParams(new FormData(form)).toString()}`;
        results.setAttribute('aria-busy', 'true');
        try {
            const response = await fetch(target, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                signal: activeRequest.signal,
            });
            if (!response.ok) throw new Error('Impossible de charger les produits.');
            const data = await response.json();
            results.innerHTML = data.html;
            document.querySelector('[data-result-count]').textContent = data.total;
            document.querySelector('[data-result-first]').textContent = data.first ?? 0;
            document.querySelector('[data-result-last]').textContent = data.last ?? 0;
            document.querySelector('[data-result-total]').textContent = data.total;
            if (updateHistory) history.replaceState({}, '', target);
            window.AOS?.refreshHard();
        } catch (error) {
            if (error.name !== 'AbortError') {
                results.innerHTML = '<p class="rounded-xl bg-red-50 p-4 text-red-700" role="alert">La recherche a échoué. Réessayez.</p>';
            }
        } finally {
            results.removeAttribute('aria-busy');
        }
    };

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        search();
    });
    form.addEventListener('input', (event) => {
        if (event.target.matches('input[type="search"]')) {
            window.clearTimeout(timer);
            timer = window.setTimeout(() => search(), 300);
        }
    });
    form.addEventListener('change', (event) => {
        if (event.target.matches('select, input[type="checkbox"], input[type="number"]')) search();
    });
    results.addEventListener('click', (event) => {
        const link = event.target.closest('.pagination a');
        if (!link) return;
        event.preventDefault();
        search(link.href);
    });
    window.addEventListener('popstate', () => {
        const params = new URLSearchParams(location.search);
        for (const control of form.elements) {
            if (!control.name) continue;
            if (control.type === 'checkbox') control.checked = params.get(control.name) === control.value;
            else control.value = params.get(control.name) || '';
        }
        search(location.href, false);
    });
})();
</script>
@endpush
