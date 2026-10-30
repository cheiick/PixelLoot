@extends('layouts.admin')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div>
        <p class="text-sm font-semibold uppercase tracking-wide text-blue-700">Accès sécurisés</p>
        <h1 class="mt-1 text-3xl font-bold text-gray-950">Administrateurs</h1>
        <p class="mt-2 text-gray-600">Seuls les administrateurs déjà connectés peuvent créer de nouveaux accès au panel.</p>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800" role="status">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800" role="alert">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold text-gray-950">Créer un compte administrateur</h2>
        <p class="mt-1 text-sm text-gray-500">Utilisez une adresse professionnelle unique et un mot de passe temporaire d’au moins 12 caractères. Transmettez-le au nouvel administrateur par un canal sécurisé.</p>
        <form action="{{ route('admin.admin-users.store') }}" method="POST" class="mt-5 grid gap-4 md:grid-cols-2">
            @csrf
            <div>
                <label for="name" class="mb-1 block text-sm font-medium text-gray-700">Nom complet</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="255" class="w-full rounded-lg border border-gray-300 px-3 py-2">
            </div>
            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-gray-700">E-mail professionnel</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" class="w-full rounded-lg border border-gray-300 px-3 py-2">
            </div>
            <div>
                <label for="password" class="mb-1 block text-sm font-medium text-gray-700">Mot de passe temporaire</label>
                <input id="password" name="password" type="password" required minlength="12" autocomplete="new-password" class="w-full rounded-lg border border-gray-300 px-3 py-2">
            </div>
            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-medium text-gray-700">Confirmer le mot de passe</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required minlength="12" autocomplete="new-password" class="w-full rounded-lg border border-gray-300 px-3 py-2">
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 font-semibold text-white hover:bg-blue-700">Créer l’administrateur</button>
            </div>
        </form>
    </section>

    <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-6 py-4"><h2 class="font-bold text-gray-950">Accès administrateurs actifs</h2></div>
        <div class="divide-y divide-gray-100">
            @foreach($admins as $admin)
                <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4">
                    <div><p class="font-semibold text-gray-900">{{ $admin->name }}</p><p class="text-sm text-gray-500">{{ $admin->email }}</p></div>
                    @if($admin->is(auth()->user()))
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">Votre compte</span>
                    @else
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Administrateur</span>
                    @endif
                </div>
            @endforeach
        </div>
    </section>
</div>
@endsection
