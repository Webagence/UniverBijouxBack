<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountResource\Pages;
use App\Models\Discount;
use App\Models\Product;
use App\Models\Universe;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class DiscountResource extends Resource
{
    protected static ?string $model = Discount::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Ventes';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations générales')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom de la remise')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('code')
                            ->label('Code promo')
                            ->placeholder('Ex: ETE2026')
                            ->maxLength(50)
                            ->unique(Discount::class, 'code', ignoreRecord: true)
                            ->helperText('Laisser vide pour une remise automatique sans code')
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                $set('code', strtoupper($state));
                            }),
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->columnSpanFull()
                            ->rows(3),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Type & Valeur')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Type de remise')
                            ->options([
                                'percentage' => 'Pourcentage (%)',
                                'fixed' => 'Montant fixe (€)',
                                'free_shipping' => 'Livraison gratuite',
                            ])
                            ->required()
                            ->live(),
                        Forms\Components\TextInput::make('value')
                            ->label('Valeur')
                            ->numeric()
                            ->prefix(fn (Forms\Get $get) => $get('type') === 'percentage' ? '%' : '€')
                            ->required(fn (Forms\Get $get) => $get('type') !== 'free_shipping')
                            ->hidden(fn (Forms\Get $get) => $get('type') === 'free_shipping'),
                        Forms\Components\TextInput::make('max_discount_amount')
                            ->label('Plafond de remise (€)')
                            ->numeric()
                            ->prefix('€')
                            ->nullable()
                            ->helperText('Montant maximum de la remise'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Conditions d\'application')
                    ->schema([
                        Forms\Components\Select::make('applies_to')
                            ->label('S\'applique à')
                            ->options([
                                'all' => 'Tous les produits',
                                'specific_products' => 'Produits spécifiques',
                                'specific_universes' => 'Univers spécifiques',
                            ])
                            ->required()
                            ->live(),
                        Forms\Components\Select::make('billing_cycle')
                            ->label('Cycle de facturation')
                            ->options([
                                'all' => 'Tous les cycles',
                                'daily' => 'Journalier',
                                'weekly' => 'Hebdomadaire',
                                'monthly' => 'Mensuel',
                                'yearly' => 'Annuel',
                            ])
                            ->default('all'),
                        Forms\Components\TextInput::make('min_order_amount')
                            ->label('Montant minimum de commande (€)')
                            ->numeric()
                            ->prefix('€')
                            ->nullable(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Produits & Univers ciblés')
                    ->schema([
                        Forms\Components\Select::make('products')
                            ->label('Produits concernés')
                            ->relationship('products', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->hidden(fn (Forms\Get $get) => $get('applies_to') !== 'specific_products'),
                        Forms\Components\Select::make('users')
                            ->label('Clients concernés')
                            ->relationship('users', 'email')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('Laisser vide pour tous les clients'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Période & Limites')
                    ->schema([
                        Forms\Components\DateTimePicker::make('valid_from')
                            ->label('Début de validité')
                            ->nullable(),
                        Forms\Components\DateTimePicker::make('valid_until')
                            ->label('Fin de validité')
                            ->nullable(),
                        Forms\Components\TextInput::make('usage_limit')
                            ->label('Nombre d\'utilisations maximum')
                            ->numeric()
                            ->nullable()
                            ->helperText('Laisser vide pour utilisation illimitée'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Discount $record) => $record->code ? "Code: {$record->code}" : 'Remise automatique'),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'percentage' => 'info',
                        'fixed' => 'success',
                        'free_shipping' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'percentage' => 'Pourcentage',
                        'fixed' => 'Montant fixe',
                        'free_shipping' => 'Livraison gratuite',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('value')
                    ->label('Valeur')
                    ->suffix(fn (Discount $record) => $record->type === 'percentage' ? '%' : '€')
                    ->sortable(),
                Tables\Columns\TextColumn::make('usage_count')
                    ->label('Utilisations')
                    ->description(fn (Discount $record) => $record->usage_limit ? "/ {$record->usage_limit}" : 'Illimité')
                    ->sortable(),
                Tables\Columns\TextColumn::make('valid_from')
                    ->label('Début')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('valid_until')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'percentage' => 'Pourcentage',
                        'fixed' => 'Montant fixe',
                        'free_shipping' => 'Livraison gratuite',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDiscounts::route('/'),
            'create' => Pages\CreateDiscount::route('/create'),
            'view' => Pages\ViewDiscount::route('/{record}'),
            'edit' => Pages\EditDiscount::route('/{record}/edit'),
        ];
    }
}
