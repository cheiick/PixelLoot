@php
    $siteName = \App\Models\SiteSetting::getValue('site_name', 'PixelLoot');
    $statusLabels = [
        'pending' => 'En attente',
        'paid' => 'Payée',
        'shipped' => 'Expédiée',
        'delivered' => 'Livrée',
    ];
    $statusColors = [
        'pending' => ['#92400e', '#fef3c7'],
        'paid' => ['#166534', '#dcfce7'],
        'shipped' => ['#1d4ed8', '#dbeafe'],
        'delivered' => ['#5b21b6', '#ede9fe'],
    ];
    [$statusColor, $statusBackground] = $statusColors[$order->status] ?? ['#334155', '#e2e8f0'];
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Mise à jour de votre commande #{{ $order->id }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f1f5f9; color:#1e293b; font-family:Arial, Helvetica, sans-serif;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%; border-collapse:collapse; background-color:#f1f5f9;">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="600" style="width:100%; max-width:600px; border-collapse:separate; border-spacing:0; background-color:#ffffff; border-radius:16px; overflow:hidden;">
                    <tr>
                        <td style="padding:28px 32px; background-color:#0f172a; border-bottom:4px solid #2563eb;">
                            <p style="margin:0; color:#93c5fd; font-size:12px; font-weight:bold; letter-spacing:2px; text-transform:uppercase;">{{ $siteName }}</p>
                            <h1 style="margin:10px 0 0; color:#ffffff; font-size:24px; line-height:1.3;">Mise à jour de votre commande</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 12px; color:#334155; font-size:16px; line-height:1.6;">Bonjour {{ $order->user->name }},</p>
                            <p style="margin:0 0 20px; color:#475569; font-size:15px; line-height:1.6;">Le statut de votre commande <strong style="color:#0f172a;">#{{ $order->id }}</strong> a été mis à jour.</p>
                            <p style="margin:0 0 28px;">
                                <span style="display:inline-block; padding:8px 14px; border-radius:999px; background-color:{{ $statusBackground }}; color:{{ $statusColor }}; font-size:13px; font-weight:bold;">
                                    {{ $statusLabels[$order->status] ?? ucfirst($order->status) }}
                                </span>
                            </p>

                            <h2 style="margin:0 0 14px; color:#0f172a; font-size:18px;">Détails de la commande</h2>
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%; border-collapse:collapse; border:1px solid #e2e8f0;">
                                <thead>
                                    <tr style="background-color:#f8fafc;">
                                        <th align="left" style="padding:12px; border-bottom:1px solid #e2e8f0; color:#64748b; font-size:12px; text-transform:uppercase;">Article</th>
                                        <th align="center" style="padding:12px; border-bottom:1px solid #e2e8f0; color:#64748b; font-size:12px; text-transform:uppercase;">Qté</th>
                                        <th align="right" style="padding:12px; border-bottom:1px solid #e2e8f0; color:#64748b; font-size:12px; text-transform:uppercase;">Prix unitaire</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                        <tr>
                                            <td style="padding:12px; border-bottom:1px solid #e2e8f0; color:#334155; font-size:14px; line-height:1.5;">{{ $item->product?->name ?? 'Produit' }}</td>
                                            <td align="center" style="padding:12px; border-bottom:1px solid #e2e8f0; color:#334155; font-size:14px;">{{ $item->quantity }}</td>
                                            <td align="right" style="padding:12px; border-bottom:1px solid #e2e8f0; color:#334155; font-size:14px; white-space:nowrap;">@money($item->price, $order->currency_code, $order->currency_symbol)</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%; margin-top:16px; border-collapse:collapse;">
                                @if($order->coupon_code)
                                    <tr>
                                        <td style="padding:5px 0; color:#64748b; font-size:14px;">Code promo <strong style="color:#334155;">{{ $order->coupon_code }}</strong></td>
                                        <td align="right" style="padding:5px 0; color:#15803d; font-size:14px; white-space:nowrap;">−@money($order->discount_amount, $order->currency_code, $order->currency_symbol)</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td style="padding:14px 0 0; border-top:1px solid #e2e8f0; color:#0f172a; font-size:16px; font-weight:bold;">Total</td>
                                    <td align="right" style="padding:14px 0 0; border-top:1px solid #e2e8f0; color:#0f172a; font-size:18px; font-weight:bold; white-space:nowrap;">@money($order->total, $order->currency_code, $order->currency_symbol)</td>
                                </tr>
                            </table>

                            <div style="margin-top:28px; padding:16px; border-radius:10px; background-color:#f8fafc;">
                                <p style="margin:0 0 6px; color:#64748b; font-size:12px; font-weight:bold; letter-spacing:1px; text-transform:uppercase;">Adresse de livraison</p>
                                <p style="margin:0; color:#334155; font-size:14px; line-height:1.6;">{{ $order->address }}</p>
                            </div>

                            <p style="margin:28px 0 0; color:#475569; font-size:14px; line-height:1.6;">Merci pour votre confiance,<br><strong style="color:#0f172a;">L’équipe {{ $siteName }}</strong></p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:18px 24px; border-top:1px solid #e2e8f0; color:#94a3b8; font-size:12px; line-height:1.6;">
                            Cet e-mail concerne la commande #{{ $order->id }}.<br>
                            Si vous avez une question, contactez notre équipe depuis le site {{ $siteName }}.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
