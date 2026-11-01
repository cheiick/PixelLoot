@extends('layouts.admin')

@section('content')
<div class="mx-auto max-w-6xl">
    <h1 class="mb-6 text-2xl font-bold">Abonnés à la newsletter</h1>
    <div class="overflow-x-auto rounded-xl bg-white shadow-sm">
        <table class="min-w-[36rem] text-left text-sm">
            <thead class="bg-gray-50 text-gray-600"><tr><th class="p-4">E-mail</th><th class="p-4">Inscription</th><th class="p-4">Statut</th><th class="p-4">Action</th></tr></thead>
            <tbody class="divide-y">
                @forelse($subscribers as $subscriber)
                    <tr>
                        <td class="p-4">{{ $subscriber->email }}</td>
                        <td class="p-4">{{ $subscriber->subscribed_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="p-4">{{ $subscriber->unsubscribed_at ? 'Désinscrit' : 'Actif' }}</td>
                        <td class="p-4">
                            <form action="{{ route('admin.newsletter.destroy', $subscriber) }}" method="POST" onsubmit="return confirm('Supprimer cet abonné ?')">
                                @csrf @method('DELETE')
                                <button class="font-semibold text-red-600 hover:underline">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-6 text-center text-gray-500">Aucun abonné.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $subscribers->links() }}</div>
</div>
@endsection
