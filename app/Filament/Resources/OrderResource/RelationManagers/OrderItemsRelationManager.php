<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Articles de la commande';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('product_name')
                    ->label('Produit')
                    ->required(),
                Forms\Components\TextInput::make('product_reference')
                    ->label('Référence'),
                Forms\Components\TextInput::make('unit_price_ht')
                    ->label('Prix unitaire HT')
                    ->numeric()
                    ->prefix('€')
                    ->required(),
                Forms\Components\TextInput::make('quantity')
                    ->label('Quantité')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('line_total_ht')
                    ->label('Total HT')
                    ->numeric()
                    ->prefix('€')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->columns([
                Tables\Columns\TextColumn::make('product_name')
                    ->label('Produit')
                    ->searchable(),
                Tables\Columns\TextColumn::make('product_reference')
                    ->label('Référence'),
                Tables\Columns\TextColumn::make('unit_price_ht')
                    ->label('Prix unit. HT')
                    ->money('EUR'),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Qté'),
                Tables\Columns\TextColumn::make('line_total_ht')
                    ->label('Total HT')
                    ->money('EUR'),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
