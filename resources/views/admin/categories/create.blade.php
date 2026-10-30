@extends('layouts.admin')

@section('title', 'Créer une catégorie')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Créer une catégorie</h1>
    @include('admin.categories.form', [
        'action' => route('admin.categories.store'),
        'method' => 'POST',
        'category' => null,
        'buttonLabel' => 'Créer la catégorie',
    ])
@endsection
