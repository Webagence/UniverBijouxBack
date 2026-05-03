<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\Universe;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations principales')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                $set('slug', Str::slug($state));
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(Product::class, 'slug', ignoreRecord: true),
                        Forms\Components\TextInput::make('reference')
                            ->label('Référence'),
                        Forms\Components\Select::make('universe_id')
                            ->label('Univers')
                            ->options(Universe::pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull()
                            ->rows(4),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Prix & Stock')
                    ->schema([
                        Forms\Components\TextInput::make('price_ht')
                            ->label('Prix HT (€)')
                            ->required()
                            ->numeric()
                            ->prefix('€')
                            ->default(0),
                        Forms\Components\TextInput::make('sale_price_ht')
                            ->label('Prix promo HT (€)')
                            ->numeric()
                            ->prefix('€')
                            ->nullable()
                            ->helperText('Laisser vide si pas de promotion'),
                        Forms\Components\TextInput::make('retail_ttc')
                            ->label('Prix public TTC conseillé (€)')
                            ->numeric()
                            ->prefix('€')
                            ->default(0),
                        Forms\Components\TextInput::make('vat_rate')
                            ->label('TVA (%)')
                            ->required()
                            ->numeric()
                            ->default(20),
                        Forms\Components\TextInput::make('stock')
                            ->required()
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('moq')
                            ->label('Quantité minimale (MOQ)')
                            ->required()
                            ->numeric()
                            ->default(3),
                        Forms\Components\TextInput::make('pack_size')
                            ->label('Conditionnement')
                            ->required()
                            ->numeric()
                            ->default(3),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Détails produit')
                    ->schema([
                        Forms\Components\TextInput::make('material')
                            ->label('Matière'),
                        Forms\Components\TextInput::make('finish')
                            ->label('Finition'),
                        Forms\Components\TextInput::make('quality_grade')
                            ->label('Qualité (grade)')
                            ->placeholder('A, A+, AA, AA+, AAA')
                            ->maxLength(10),
                        Forms\Components\Select::make('tag')
                            ->options([
                                'Nouveauté' => 'Nouveauté',
                                'Best-seller' => 'Best-seller',
                                'Réassort' => 'Réassort',
                                'Édition limitée' => 'Édition limitée',
                            ])
                            ->nullable(),
                        Forms\Components\Toggle::make('is_new')
                            ->label('Nouveauté'),
                        Forms\Components\Toggle::make('active')
                            ->label('Actif')
                            ->default(true),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Images')
                    ->schema([
                        FileUpload::make('images')
                            ->label('Images du produit')
                            ->image()
                            ->disk('public')
                            ->directory('products')
                            ->multiple()
                            ->reorderable()
                            ->maxFiles(10)
                            ->maxSize(5120)
                            ->panelLayout('grid')
                            ->columnSpanFull()
                            ->default([]),
                    ]),

                Forms\Components\Section::make('Variations')
                    ->description('Tailles, formes, couleurs... Le client pourra choisir parmi les options.')
                    ->schema([
                        Forms\Components\Repeater::make('variations')
                            ->label('Variations du produit')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nom de la variation')
                                    ->placeholder('Ex : Taille, Forme, Couleur')
                                    ->required(),
                                Forms\Components\TagsInput::make('options')
                                    ->label('Options (une par tag)')
                                    ->placeholder('Ex : 4mm, 6mm, 8mm')
                                    ->separator(',')
                                    ->required(),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                            ->defaultItems(0)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail_url')
                    ->label('Image')
                    ->circular()
                    ->size(50),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Product $record) => $record->reference),
                Tables\Columns\TextColumn::make('universe.name')
                    ->label('Univers')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_ht')
                    ->label('Prix HT')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state <= 0 => 'danger',
                        $state <= 10 => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('tag')
                    ->label('Tag')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Nouveauté' => 'info',
                        'Best-seller' => 'success',
                        'Réassort' => 'warning',
                        'Édition limitée' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_new')
                    ->label('Nouveau')
                    ->boolean(),
                Tables\Columns\IconColumn::make('active')
                    ->label('Actif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('universe_id')
                    ->label('Univers')
                    ->relationship('universe', 'name'),
                Tables\Filters\SelectFilter::make('tag')
                    ->options([
                        'Nouveauté' => 'Nouveauté',
                        'Best-seller' => 'Best-seller',
                        'Réassort' => 'Réassort',
                        'Édition limitée' => 'Édition limitée',
                    ]),
                Tables\Filters\TernaryFilter::make('is_new')
                    ->label('Nouveauté'),
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Actif'),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
