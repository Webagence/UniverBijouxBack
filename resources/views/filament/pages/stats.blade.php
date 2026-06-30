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
            <div class="flex items-end gap-2 h-32">
                @foreach(array_reverse($data['byDay']) as $day => $count)
                    @php
                        $max = max($data['byDay']) ?: 1;
                        $h = round($count / $max * 100);
                    @endphp
                    <div class="flex-1 flex flex-col items-center">
                        <div class="w-full bg-primary-500 rounded-t" style="height: {{ $h }}%"></div>
                        <div class="text-[10px] text-gray-500 mt-1 transform -rotate-45 origin-left">{{ substr($day, 5) }}</div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-500">Aucune donnée</p>
        @endif
    </x-filament::section>
</x-filament-panels::page>
