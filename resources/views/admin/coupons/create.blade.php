@extends('layouts.admin')

@section('title', 'Créer un coupon')

@section('content')
    @if ($errors->any())
        <div class="mb-4 rounded border border-red-300 bg-red-50 px-4 py-3 text-red-800">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.coupons.store') }}" method="POST" class="max-w-2xl rounded bg-white p-6 shadow">
        @csrf
        <div class="mb-4">
            <label for="code" class="block font-medium mb-1">Code</label>
            <input type="text" name="code" id="code" value="{{ old('code') }}" class="form-input w-full" required>
        </div>

        <div class="mb-4">
            <label for="type" class="block font-medium mb-1">Type</label>
            <select name="type" id="type" class="form-select w-full" required>
                <option value="percentage" {{ old('type')=='percentage' ? 'selected' : '' }}>Pourcentage</option>
                <option value="fixed" {{ old('type')=='fixed' ? 'selected' : '' }}>Montant fixe</option>
            </select>
        </div>

        <div class="mb-4">
            <label for="value" class="block font-medium mb-1">Valeur</label>
            <input type="number" step="0.01" name="value" id="value" value="{{ old('value') }}" class="form-input w-full" required>
        </div>

        <div class="mb-4">
            <label for="min_purchase" class="block font-medium mb-1">Montant minimum d'achat</label>
            <input type="number" name="min_purchase" id="min_purchase" value="{{ old('min_purchase') }}" class="form-input w-full">
        </div>

        <div class="mb-4">
            <label for="usage_limit" class="block font-medium mb-1">Limite d’utilisation</label>
            <input type="number" name="usage_limit" id="usage_limit" value="{{ old('usage_limit') }}" class="form-input w-full">
        </div>

        <div class="mb-4">
            <label for="valid_from" class="block font-medium mb-1">Valide du (JJ/MM/AAAA)</label>
            <input type="date" name="valid_from" id="valid_from" value="{{ old('valid_from') }}" class="form-input w-full">
        </div>

        <div class="mb-4">
            <label for="valid_until" class="block font-medium mb-1">Valide au (JJ/MM/AAAA)</label>
            <input type="date" name="valid_until" id="valid_until" value="{{ old('valid_until') }}" class="form-input w-full">
        </div>

        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </form>
@endsection
