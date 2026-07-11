<?php

namespace App\Filament\Pages;

use App\Traits\HasFilamentPageAccess;
use App\Models\PageView;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class Stats extends Page
{
    use HasFilamentPageAccess;
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Statistiques';

    protected static ?string $navigationGroup = 'Paramètres';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.stats';

    public function getViewData(): array
    {
        $total = PageView::count();
        $today = PageView::whereDate('visited_at', today())->count();
        $uniqueIps = PageView::distinct('ip')->count('ip');

        $bySite = PageView::select('site', DB::raw('count(*) as c'))
            ->groupBy('site')->orderByDesc('c')->get()->pluck('c', 'site')->toArray();

        // Generate 14 days with 0 for empty days
        $byDay = [];
        for ($i = 13; $i >= 0; $i--) {
            $day = today()->subDays($i)->format('Y-m-d');
            $byDay[$day] = 0;
        }
        $realData = PageView::select(DB::raw('DATE(visited_at) as day'), DB::raw('count(*) as c'))
            ->where('visited_at', '>=', today()->subDays(13))
            ->groupBy('day')->get()->pluck('c', 'day')->toArray();
        foreach ($realData as $day => $count) {
            if (isset($byDay[$day])) {
                $byDay[$day] = $count;
            }
        }

        $topPaths = PageView::select('path', DB::raw('count(*) as c'))
            ->groupBy('path')->orderByDesc('c')->take(10)->get()->toArray();

        return [
            'total' => $total,
            'today' => $today,
            'uniqueIps' => $uniqueIps,
            'bySite' => $bySite,
            'byDay' => $byDay,
            'topPaths' => $topPaths,
        ];
    }
}
