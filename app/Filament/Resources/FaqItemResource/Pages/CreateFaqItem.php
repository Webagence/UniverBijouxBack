<?php

namespace App\Filament\Resources\FaqItemResource\Pages;

use App\Filament\Resources\FaqItemResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFaqItem extends CreateRecord
{
    protected static string $resource = FaqItemResource::class;
}
