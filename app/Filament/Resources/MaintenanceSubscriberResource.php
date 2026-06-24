<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceSubscriberResource\Pages;
use App\Models\MaintenanceSubscriber;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MaintenanceSubscriberResource extends Resource
{
    protected static ?string $model = MaintenanceSubscriber::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';

    protected static ?string $navigationLabel = 'Inscrits Maintenance';

    protected static ?string $navigationGroup = 'Paramètres';

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('subscribed_at')
                    ->label('Inscrit le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('subscribed_at', 'desc')
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaintenanceSubscribers::route('/'),
        ];
    }
}
