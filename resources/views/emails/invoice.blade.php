<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #1a1a1a; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; padding: 20px 0; border-bottom: 3px solid #3a1a25; }
        .header h1 { color: #3a1a25; margin: 0; font-size: 24px; }
        .header p { color: #9a7a5a; margin: 5px 0 0; font-size: 14px; }
        .content { padding: 20px 0; }
        .content p { margin: 10px 0; }
        .invoice-info { background: #faf7f2; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .invoice-info p { margin: 5px 0; }
        .total { font-size: 20px; font-weight: bold; color: #3a1a25; }
        .footer { text-align: center; padding: 20px 0; border-top: 1px solid #e8e0d6; font-size: 12px; color: #888; }
        .button { display: inline-block; padding: 12px 24px; background: #3a1a25; color: #fff; text-decoration: none; border-radius: 5px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $order?->reference ?? 'Votre commande' }}</h1>
            <p>Votre facture est disponible</p>
        </div>

        <div class="content">
            <p>Bonjour {{ $user?->name ?? 'Cher client' }},</p>
            <p>Nous vous remercions pour votre commande. Votre paiement a bien été reçu et votre facture est disponible ci-jointe.</p>

            <div class="invoice-info">
                <p><strong>Facture :</strong> {{ $invoice->invoice_number }}</p>
                <p><strong>Commande :</strong> {{ $order?->reference ?? '—' }}</p>
                <p><strong>Date :</strong> {{ $invoice->issued_at->format('d/m/Y') }}</p>
                <p class="total">Total TTC : {{ number_format($invoice->total_ttc, 2, ',', ' ') }} €</p>
            </div>

            <p>La facture PDF est jointe à cet email. Vous pouvez également la consulter dans votre espace professionnel.</p>

            <p style="text-align: center;">
                <a href="{{ config('app.frontend_url') }}/commandes/{{ $order?->id ?? '' }}" class="button" style="color: #ffffff !important; text-decoration: none;">
                    Voir ma commande
                </a>
            </p>
        </div>

        <div class="footer">
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre directement.</p>
            <p>{{ config('app.name', 'FranceGems') }} · Grossiste B2B</p>
        </div>
    </div>
</body>
</html>
