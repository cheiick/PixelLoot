@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="mb-10 max-w-2xl">
        <p class="text-sm font-semibold uppercase tracking-wide text-blue-700">Nous sommes là pour vous</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-gray-950 sm:text-4xl">Contactez-nous</h1>
        <p class="mt-3 leading-7 text-gray-600">Une question sur un produit ou une commande ? Envoyez-nous un message, notre équipe vous répondra dès que possible.</p>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800" role="status">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800" role="alert">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <div class="grid gap-8 lg:grid-cols-[0.8fr_1.2fr]">
        <aside class="h-fit rounded-2xl bg-slate-950 p-7 text-white shadow-sm">
            <h2 class="text-xl font-bold">Nos coordonnées</h2>
            <p class="mt-2 text-sm leading-6 text-slate-300">Vous pouvez également nous joindre directement avec les coordonnées ci-dessous.</p>
            @php
                $contactEmail = \App\Models\SiteSetting::getValue('footer_email', 'contact@monshop.com');
                $contactPhone = \App\Models\SiteSetting::getValue('footer_phone', '01 23 45 67 89');
            @endphp
            <div class="mt-7 space-y-5">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">E-mail</p>
                    <a href="mailto:{{ $contactEmail }}" class="mt-1 inline-block break-all font-medium hover:text-blue-300">{{ $contactEmail }}</a>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Téléphone</p>
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $contactPhone) }}" class="mt-1 inline-block font-medium hover:text-blue-300">{{ $contactPhone }}</a>
                </div>
            </div>
        </aside>

        <form action="{{ route('contact.store') }}" method="POST" class="space-y-5 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
            @csrf
            <div class="absolute -left-[10000px]" aria-hidden="true">
                <label for="website">Ne pas remplir ce champ</label>
                <input id="website" type="text" name="website" tabindex="-1" autocomplete="off">
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700">Nom</label>
                    <input id="name" type="text" name="name" value="{{ old('name', auth()->user()?->name) }}" required maxlength="255" autocomplete="name" class="w-full rounded-xl border border-gray-300 px-4 py-3 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700">Adresse e-mail</label>
                    <input id="email" type="email" name="email" value="{{ old('email', auth()->user()?->email) }}" required maxlength="255" autocomplete="email" class="w-full rounded-xl border border-gray-300 px-4 py-3 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label for="subject" class="mb-1.5 block text-sm font-medium text-gray-700">Sujet</label>
                <input id="subject" type="text" name="subject" value="{{ old('subject') }}" required maxlength="255" class="w-full rounded-xl border border-gray-300 px-4 py-3 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                @error('subject')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="message" class="mb-1.5 block text-sm font-medium text-gray-700">Message</label>
                <textarea id="message" name="message" rows="6" required minlength="10" maxlength="5000" class="w-full rounded-xl border border-gray-300 px-4 py-3 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">{{ old('message') }}</textarea>
                @error('message')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-6 py-3 font-semibold text-white transition hover:bg-blue-700 sm:w-auto">Envoyer le message</button>
        </form>
    </div>
</div>
@endsection
