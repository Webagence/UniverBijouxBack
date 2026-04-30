<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\Ticket;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Produits', Product::count())
                ->description('Produits au catalogue')
                ->descriptionIcon('heroicon-o-rectangle-stack')
                ->color('success'),
            Stat::make('Commandes', Order::count())
                ->description(Order::where('status', Order::STATUS_PENDING)->count() . ' en attente')
                ->descriptionIcon('heroicon-o-shopping-cart')
                ->color('primary'),
            Stat::make('Comptes pros', User::whereHas('roles', fn ($q) => $q->where('name', 'pro'))->count())
                ->description(User::where('approved', false)->count() . ' en attente')
                ->descriptionIcon('heroicon-o-users')
                ->color('warning'),
            Stat::make('Tickets', Ticket::where('status', 'open')->count())
                ->description('Tickets ouverts')
                ->descriptionIcon('heroicon-o-ticket')
                ->color('danger'),
        ];
    }
}
