<div class="space-y-2">
    @php
        $address = $getState() ?? [];
    @endphp
    @if(is_array($address))
        <p><strong>{{ $address['name'] ?? 'N/A' }}</strong></p>
        <p>{{ $address['address'] ?? '' }}</p>
        <p>{{ $address['postal_code'] ?? '' }} {{ $address['city'] ?? '' }}</p>
        <p>{{ $address['country'] ?? '' }}</p>
    @else
        <p class="text-gray-500">Adresse non disponible</p>
    @endif
</div>
