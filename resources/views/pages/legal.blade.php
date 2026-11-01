@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-16 px-4">
    <h1 class="mb-8 text-3xl font-bold text-gray-900 sm:text-4xl">Mentions légales & CGV</h1>
    <div class="prose prose-lg text-gray-600 space-y-6">
        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">Mentions légales</h2>
            <p>MonShop est édité par la société MonShop SAS, au capital de 10 000€, immatriculée au RCS de Paris sous le numéro 123 456 789.</p>
            <p>Siège social : 123 Rue du Commerce, 75001 Paris, France.</p>
        </section>
        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">Conditions Générales de Vente</h2>
            <p>Les présentes CGV régissent les relations contractuelles entre MonShop et ses clients. Toute commande implique l'acceptation sans réserve des présentes CGV.</p>
        </section>
        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">Politique de confidentialité</h2>
            <p>Conformément au RGPD, vos données personnelles sont protégées et ne sont jamais revendues à des tiers.</p>
        </section>
    </div>
</div>
@endsection