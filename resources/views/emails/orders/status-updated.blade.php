<h1>Mise à jour de votre commande #{{ $order->id }}</h1>

<p>Bonjour {{ $order->user->name }},</p>
@php($statusLabels = ['pending' => 'En attente', 'paid' => 'Payée', 'shipped' => 'Expédiée', 'delivered' => 'Livrée'])
<p>Le statut de votre commande est maintenant : <strong>{{ $statusLabels[$order->status] ?? ucfirst($order->status) }}</strong>.</p>

<h2>Détails de la commande</h2>
<ul>
    @foreach($order->items as $item)
        <li>
            {{ $item->product?->name ?? 'Produit' }} —
            {{ $item->quantity }} × @money($item->price, $order->currency_code, $order->currency_symbol)
        </li>
    @endforeach
</ul>

@if($order->coupon_code)
    <p>Code promo : {{ $order->coupon_code }}</p>
    <p>Réduction : @money($order->discount_amount, $order->currency_code, $order->currency_symbol)</p>
@endif

<p><strong>Total : @money($order->total, $order->currency_code, $order->currency_symbol)</strong></p>
<p>Adresse de livraison : {{ $order->address }}</p>
