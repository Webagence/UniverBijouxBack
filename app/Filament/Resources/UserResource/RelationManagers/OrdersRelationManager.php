<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'Commandes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('reference')
                    ->label('Référence')
                    ->disabled()
                    ->dehydrated(),
                Forms\Components\Select::make('status')
                    ->label('Statut')
                    ->options([
                        Order::STATUS_PENDING => 'En attente',
                        Order::STATUS_CONFIRMED => 'Confirmée',
                        Order::STATUS_PREPARING => 'En préparation',
                        Order::STATUS_SHIPPED => 'Expédiée',
                        Order::STATUS_DELIVERED => 'Livrée',
                        Order::STATUS_CANCELLED => 'Annulée',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('total_ttc')
                    ->label('Total TTC')
                    ->numeric()
                    ->prefix('€')
                    ->readonly()
                    ->dehydrated(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reference')
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Order::STATUS_PENDING => 'warning',
                        Order::STATUS_CONFIRMED => 'info',
                        Order::STATUS_PREPARING => 'primary',
                        Order::STATUS_SHIPPED => 'success',
                        Order::STATUS_DELIVERED => 'success',
                        Order::STATUS_CANCELLED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Order::STATUS_PENDING => 'En attente',
                        Order::STATUS_CONFIRMED => 'Confirmée',
                        Order::STATUS_PREPARING => 'En préparation',
                        Order::STATUS_SHIPPED => 'Expédiée',
                        Order::STATUS_DELIVERED => 'Livrée',
                        Order::STATUS_CANCELLED => 'Annulée',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('total_ttc')
                    ->label('Total TTC')
                    ->money('EUR'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }
}
