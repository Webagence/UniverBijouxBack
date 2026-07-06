<x-filament-panels::page>
    @php $data = $this->getViewData(); @endphp

    <div class="grid grid-cols-3 gap-6 mb-8">
        <div class="p-5 bg-primary-50 rounded-xl text-center dark:bg-primary-900/20">
            <div class="text-3xl font-bold text-primary-600">{{ number_format($data['total']) }}</div>
            <div class="text-xs text-primary-600 mt-1">Vues totales</div>
        </div>
        <div class="p-5 bg-success-50 rounded-xl text-center dark:bg-success-900/20">
            <div class="text-3xl font-bold text-success-600">{{ $data['today'] }}</div>
            <div class="text-xs text-success-600 mt-1">Aujourd'hui</div>
        </div>
        <div class="p-5 bg-info-50 rounded-xl text-center dark:bg-info-900/20">
            <div class="text-3xl font-bold text-info-600">{{ number_format($data['uniqueIps']) }}</div>
            <div class="text-xs text-info-600 mt-1">Visiteurs uniques (IP)</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Par site --}}
        <x-filament::section heading="Vues par site">
            <div class="space-y-3">
                @forelse($data['bySite'] as $site => $count)
                    @php
                        $total = $data['total'] ?: 1;
                        $pct = round($count / $total * 100, 1);
                        $labels = ['portail' => 'Portail (francegems.com)', 'bijoux' => 'Boutique Bijoux', 'pierres' => 'Boutique Pierres'];
                    @endphp
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span>{{ $labels[$site] ?? $site }}</span>
                            <span class="font-medium">{{ number_format($count) }} ({{ $pct }}%)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                            <div class="bg-primary-500 h-2 rounded-full" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Aucune donnée</p>
                @endforelse
            </div>
        </x-filament::section>

        {{-- Pages populaires --}}
        <x-filament::section heading="Pages les plus visitées">
            <div class="space-y-2">
                @forelse($data['topPaths'] as $p)
                    <div class="flex justify-between text-sm py-1 border-b dark:border-gray-700 last:border-0">
                        <span class="truncate max-w-xs">{{ $p['path'] ?: '/' }}</span>
                        <span class="font-medium ml-2">{{ $p['c'] }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Aucune donnée</p>
                @endforelse
            </div>
        </x-filament::section>
    </div>

    {{-- 14 derniers jours --}}
    <x-filament::section heading="Vues (14 derniers jours)" class="mt-8">
        @if(count($data['byDay']) > 0)
            @php $max = max($data['byDay']) ?: 1; @endphp
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b dark:border-gray-700">
                            <th class="text-left py-2 px-2">Jour</th>
                            <th class="text-right py-2 px-2">Vues</th>
                            <th class="py-2 px-2 w-full"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(array_reverse($data['byDay']) as $day => $count)
                            @php
                                $pct = $max > 0 ? round($count / $max * 100) : 0;
                                $isToday = $day === date('Y-m-d');
                            @endphp
                            <tr class="border-b dark:border-gray-700 last:border-0">
                                <td class="py-1.5 px-2 text-xs {{ $isToday ? 'font-semibold' : '' }}">{{ $day }}</td>
                                <td class="py-1.5 px-2 text-xs text-right font-mono">{{ $count }}</td>
                                <td class="py-1.5 px-2 w-full">
                                    <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-5 overflow-hidden">
                                        <div class="h-full rounded-full {{ $isToday ? 'bg-warning-500' : 'bg-primary-500' }}" style="width: {{ $pct }}%; min-width: {{ $count > 0 ? '4px' : '0' }}"></div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-gray-500">Aucune donnée</p>
        @endif
    </x-filament::section>
</x-filament-panels::page>
