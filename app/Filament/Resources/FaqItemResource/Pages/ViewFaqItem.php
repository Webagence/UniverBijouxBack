<?php

namespace App\Filament\Resources\FaqItemResource\Pages;

use App\Filament\Resources\FaqItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewFaqItem extends ViewRecord
{
    protected static string $resource = FaqItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
