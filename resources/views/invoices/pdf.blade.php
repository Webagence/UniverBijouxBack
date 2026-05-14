<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $invoiceNumber }}</title>
    <style>
        @page { size: A4; margin: 20mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1a1a1a;
            padding: 40px;
            font-size: 12px;
            line-height: 1.5;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 20px;
            border-bottom: 3px solid #3a1a25;
            margin-bottom: 24px;
        }
        .header-left img { height: 50px; width: auto; }
        .header-left h1 { font-size: 22px; color: #3a1a25; margin-bottom: 4px; }
        .header-left .tagline { color: #9a7a5a; font-size: 11px; letter-spacing: 1px; text-transform: uppercase; }
        .header-right { text-align: right; }
        .header-right h2 { font-size: 24px; color: #9a7a5a; font-weight: 300; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 8px; }
        .header-right p { font-size: 12px; color: #666; }
        .header-right .ref { font-size: 14px; font-weight: 600; color: #3a1a25; margin-top: 4px; }

        .info-blocks {
            display: flex;
            justify-content: space-between;
            gap: 30px;
            margin-bottom: 24px;
        }
        .info-block {
            flex: 1;
            padding: 12px 16px;
            background: #faf7f2;
            border-left: 3px solid #9a7a5a;
        }
        .info-block h3 {
            font-size: 9px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #9a7a5a;
            margin-bottom: 8px;
            font-weight: 600;
        }
        .info-block p { font-size: 11px; color: #333; margin-bottom: 2px; }
        .info-block .name { font-weight: 600; font-size: 12px; color: #1a1a1a; }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        table.items thead th {
            background: #3a1a25;
            color: #fff;
            padding: 8px 10px;
            font-size: 9px;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-weight: 600;
            text-align: left;
        }
        table.items thead th:not(:first-child) { text-align: right; }
        table.items thead th:nth-child(2) { text-align: center; }
        table.items tbody td {
            padding: 8px 10px;
            border-bottom: 1px solid #e8e0d6;
            font-size: 11px;
        }
        table.items tbody td:not(:first-child) { text-align: right; }
        table.items tbody td:nth-child(2) { text-align: center; }
        table.items tbody tr:nth-child(even) { background: #faf7f2; }

        .totals {
            margin-left: auto;
            width: 280px;
            margin-bottom: 24px;
        }
        .totals table { width: 100%; border-collapse: collapse; }
        .totals td { padding: 6px 10px; font-size: 11px; border-bottom: 1px solid #e8e0d6; }
        .totals .label { color: #666; }
        .totals .value { text-align: right; font-weight: 500; }
        .totals .grand-total {
            background: #3a1a25;
            color: #fff;
        }
        .totals .grand-total td {
            padding: 10px;
            font-size: 14px;
            font-weight: 700;
            border: none;
        }
        .totals .grand-total .value { color: #f0d9a8; }

        .paid-badge {
            display: inline-block;
            background: #22c55e;
            color: #fff;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 16px;
        }

        .footer {
            margin-top: 40px;
            padding-top: 16px;
            border-top: 1px solid #e8e0d6;
            font-size: 9px;
            color: #888;
            text-align: center;
            line-height: 1.8;
        }
        .footer strong { color: #3a1a25; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            @if($logoPath)
                <img src="{{ $logoPath }}" alt="{{ $brandName }}">
            @else
                <h1>{{ $brandName }}</h1>
            @endif
            <div class="tagline">{{ $brandTagline }}</div>
        </div>
        <div class="header-right">
            <h2>Facture</h2>
            <p class="ref">N° {{ $invoiceNumber }}</p>
            <p>Date : {{ $date }}</p>
        </div>
    </div>

    <div class="paid-badge">PAYÉE</div>

    <div class="info-blocks">
        <div class="info-block">
            <h3>Émetteur</h3>
            <p class="name">{{ $brandName }}</p>
            @if($brandAddress) <p>{{ $brandAddress }}</p> @endif
            @if($brandSiret) <p>SIRET : {{ $brandSiret }}</p> @endif
        </div>
        <div class="info-block">
            <h3>Facturé à</h3>
            @if($buyerCompany) <p class="name">{{ $buyerCompany }}</p> @endif
            @if($buyerContact) <p>{{ $buyerContact }}</p> @endif
            @if($buyerAddress) <p>{!! $buyerAddress !!}</p> @endif
            @if($buyerSiret) <p>SIRET : {{ $buyerSiret }}</p> @endif
            @if($buyerVat) <p>TVA intracom. : {{ $buyerVat }}</p> @endif
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Désignation</th>
                <th>Qté</th>
                <th>PU HT</th>
                <th>Total HT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lines as $line)
                <tr>
                    <td>{{ $line['name'] }}@if($line['reference'])<br><span style="color:#999;font-size:10px">Réf. {{ $line['reference'] }}</span>@endif</td>
                    <td>{{ $line['quantity'] }}</td>
                    <td>{{ $line['unit_price_ht'] }} €</td>
                    <td>{{ $line['line_total_ht'] }} €</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td class="label">Sous-total HT</td>
                <td class="value">{{ number_format($order->subtotal_ht, 2, ',', ' ') }} €</td>
            </tr>
            <tr>
                <td class="label">Livraison HT</td>
                <td class="value">{{ number_format($order->shipping_ht, 2, ',', ' ') }} €</td>
            </tr>
            <tr>
                <td class="label">TVA ({{ $vatRate }}%)</td>
                <td class="value">{{ number_format($order->vat_amount, 2, ',', ' ') }} €</td>
            </tr>
            <tr class="grand-total">
                <td>Total TTC</td>
                <td class="value">{{ number_format($order->total_ttc, 2, ',', ' ') }} €</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p><strong>{{ $brandName }}</strong> · {{ $brandTagline }}
        @if($brandAddress) · {{ $brandAddress }} @endif
        @if($brandSiret) · SIRET {{ $brandSiret }} @endif</p>
        <p>TVA applicable selon la législation en vigueur · Exonération de TVA intracommunautaire si numéro valide fourni</p>
        <p>Conditions de paiement : 30 jours date de facture · Pas d'escompte pour paiement anticipé</p>
        <p>En cas de retard de paiement, des pénalités égales à 3 fois le taux d'intérêt légal seront appliquées</p>
    </div>
</body>
</html>
