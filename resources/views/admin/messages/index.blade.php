@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div>
        <p class="text-sm font-semibold uppercase tracking-wide text-blue-700">Service client</p>
        <h1 class="mt-1 text-3xl font-bold text-gray-950">Messages de contact</h1>
        <p class="mt-2 text-gray-600">Les demandes envoyées depuis le formulaire public sont conservées ici.</p>
    </div>
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800" role="status">{{ session('success') }}</div>
    @endif

    <div class="space-y-4">
        @forelse($messages as $contactMessage)
            <article class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-950">{{ $contactMessage->subject }}</h2>
                        <p class="mt-1 text-sm text-gray-600">{{ $contactMessage->name }} · <a class="text-blue-700 hover:underline" href="mailto:{{ $contactMessage->email }}">{{ $contactMessage->email }}</a></p>
                        <p class="mt-1 text-xs text-gray-500">{{ $contactMessage->created_at->format('d/m/Y à H:i') }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="mailto:{{ $contactMessage->email }}?subject={{ rawurlencode('Re: ' . $contactMessage->subject) }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Répondre</a>
                        <form action="{{ route('admin.messages.destroy', $contactMessage) }}" method="POST" onsubmit="return confirm('Supprimer ce message ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-lg border border-red-200 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">Supprimer</button>
                        </form>
                    </div>
                </div>
                <p class="mt-4 whitespace-pre-line border-t border-gray-100 pt-4 text-sm leading-6 text-gray-700">{{ $contactMessage->message }}</p>
            </article>
        @empty
            <div class="rounded-2xl border border-gray-100 bg-white px-6 py-12 text-center text-gray-500 shadow-sm">Aucun message reçu pour le moment.</div>
        @endforelse
    </div>

    @if($messages->hasPages())
        <div>{{ $messages->links() }}</div>
    @endif
</div>
@endsection
