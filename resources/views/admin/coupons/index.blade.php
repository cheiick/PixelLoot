@extends('layouts.admin')

@section('title', 'Gestion des coupons')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-xl font-semibold">Coupons</h1>
        <a href="{{ route('admin.coupons.create') }}" class="rounded bg-blue-600 px-4 py-2 text-center text-white hover:bg-blue-700">Créer un coupon</a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded border border-green-300 bg-green-50 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto rounded bg-white">
    <table class="min-w-[56rem] border border-gray-200">
        <thead>
            <tr>
                <th class="px-4 py-2 text-left">Code</th>
                <th class="px-4 py-2 text-left">Type</th>
                <th class="px-4 py-2 text-right">Valeur</th>
                <th class="px-4 py-2 text-right">Montant min</th>
                <th class="px-4 py-2 text-right">Limite d’utilisation</th>
                <th class="px-4 py-2 text-right">Utilisé</th>
                <th class="px-4 py-2 text-left">Valide du</th>
                <th class="px-4 py-2 text-left">Valide au</th>
                <th class="px-4 py-2 text-center">Actif</th>
                <th class="px-4 py-2"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($coupons as $coupon)
                <tr class="border-t">
                    <td class="px-4 py-2">{{ $coupon->code }}</td>
                    <td class="px-4 py-2">{{ ucfirst($coupon->type) }}</td>
                    <td class="px-4 py-2 text-right">
                        @if($coupon->type === 'percentage')
                            {{ rtrim(rtrim(number_format((float) $coupon->value, 2, ',', ' '), '0'), ',') }} %
                        @else
                            @money($coupon->value)
                        @endif
                    </td>
                    <td class="px-4 py-2 text-right">@money($coupon->min_purchase ?? 0)</td>
                    <td class="px-4 py-2 text-right">{{ $coupon->usage_limit ?? '∞' }}</td>
                    <td class="px-4 py-2 text-right">{{ $coupon->used_count }}</td>
                    <td class="px-4 py-2">{{ optional($coupon->valid_from)->format('d/m/Y') }}</td>
                    <td class="px-4 py-2">{{ optional($coupon->valid_until)->format('d/m/Y') }}</td>
                    <td class="px-4 py-2 text-center">
                        @if($coupon->is_active)
                            ✅
                        @else
                            ❌
                        @endif
                    </td>
                    <td class="px-4 py-2 text-right">
                        <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" onsubmit="return confirm('Supprimer ce coupon?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="text-center py-4">Aucun coupon trouvé.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>

    <div class="mt-4">
        {{ $coupons->links() }}
    </div>
@endsection
