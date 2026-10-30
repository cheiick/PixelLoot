@extends('layouts.admin')

@section('title', 'Modifier une catégorie')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Modifier la catégorie</h1>
    @include('admin.categories.form', [
        'action' => route('admin.categories.update', $category),
        'method' => 'PUT',
        'category' => $category,
        'buttonLabel' => 'Enregistrer les modifications',
    ])
@endsection
