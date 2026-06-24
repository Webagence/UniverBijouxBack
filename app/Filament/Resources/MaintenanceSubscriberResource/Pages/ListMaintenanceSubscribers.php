<?php

namespace App\Filament\Resources\MaintenanceSubscriberResource\Pages;

use App\Filament\Resources\MaintenanceSubscriberResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMaintenanceSubscribers extends ListRecords
{
    protected static string $resource = MaintenanceSubscriberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label('Exporter CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $subscribers = \App\Models\MaintenanceSubscriber::orderBy('subscribed_at', 'desc')->get();
                    $csv = "email,inscrit_le\n";
                    foreach ($subscribers as $s) {
                        $csv .= "{$s->email},{$s->subscribed_at}\n";
                    }
                    return response()->streamDownload(function () use ($csv) {
                        echo $csv;
                    }, 'inscrits-maintenance.csv', ['Content-Type' => 'text/csv']);
                }),
        ];
    }
}
