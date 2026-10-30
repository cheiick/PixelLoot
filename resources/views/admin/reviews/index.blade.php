@extends('layouts.admin')

@section('content')
<div class="mx-auto max-w-6xl">
    <h1 class="mb-6 text-2xl font-bold">Modération des avis</h1>
    <div class="space-y-4">
        @forelse($reviews as $review)
            <article class="rounded-xl bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="font-semibold">{{ $review->product->name }} · {{ $review->user->name }}</h2>
                        <p class="mt-1 text-amber-600">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</p>
                        <p class="mt-3 whitespace-pre-line text-gray-700">{{ $review->comment }}</p>
                        <p class="mt-2 text-xs text-gray-500">Statut : {{ $review->status }} · {{ $review->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="flex gap-2">
                        @if($review->status !== 'approved')
                            <form action="{{ route('admin.reviews.update', $review) }}" method="POST">
                                @csrf @method('PATCH') <input type="hidden" name="status" value="approved">
                                <button class="rounded-lg bg-green-600 px-3 py-2 text-sm font-semibold text-white">Approuver</button>
                            </form>
                        @endif
                        @if($review->status !== 'rejected')
                            <form action="{{ route('admin.reviews.update', $review) }}" method="POST">
                                @csrf @method('PATCH') <input type="hidden" name="status" value="rejected">
                                <button class="rounded-lg bg-amber-600 px-3 py-2 text-sm font-semibold text-white">Refuser</button>
                            </form>
                        @endif
                        <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" onsubmit="return confirm('Supprimer cet avis ?')">
                            @csrf @method('DELETE')
                            <button class="rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white">Supprimer</button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <p class="rounded-xl bg-white p-6 text-gray-600">Aucun avis à modérer.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $reviews->links() }}</div>
</div>
@endsection
