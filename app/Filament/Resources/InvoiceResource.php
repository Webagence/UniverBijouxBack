<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Factures';

    protected static ?string $navigationGroup = 'Ventes';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations')
                    ->schema([
                        Forms\Components\TextInput::make('invoice_number')
                            ->label('Numéro de facture')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('order_id')
                            ->label('Commande')
                            ->relationship('order', 'reference')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $order = Order::find($state);
                                    if ($order) {
                                        $set('user_id', $order->user_id);
                                        $set('total_ht', $order->subtotal_ht);
                                        $set('vat_amount', $order->vat_amount);
                                        $set('total_ttc', $order->total_ttc);
                                    }
                                }
                            }),
                        Forms\Components\Select::make('user_id')
                            ->label('Client')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\DateTimePicker::make('issued_at')
                            ->label('Date d\'émission')
                            ->default(now())
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Montants')
                    ->schema([
                        Forms\Components\TextInput::make('total_ht')
                            ->label('Total HT')
                            ->numeric()
                            ->prefix('€')
                            ->required(),
                        Forms\Components\TextInput::make('vat_amount')
                            ->label('TVA')
                            ->numeric()
                            ->prefix('€')
                            ->required(),
                        Forms\Components\TextInput::make('total_ttc')
                            ->label('Total TTC')
                            ->numeric()
                            ->prefix('€')
                            ->required(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('N° facture')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('order.reference')
                    ->label('Commande')
                    ->searchable()
                    ->url(fn (Invoice $record): ?string => $record->order_id ? OrderResource::getUrl('view', ['record' => $record->order_id]) : null),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Client')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_ht')
                    ->label('Total HT')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('vat_amount')
                    ->label('TVA')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_ttc')
                    ->label('Total TTC')
                    ->money('EUR')
                    ->sortable()
                    ->color('success'),
                Tables\Columns\TextColumn::make('issued_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('issued_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('order')
                    ->relationship('order', 'reference')
                    ->label('Commande'),
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->label('Client'),
                Tables\Filters\Filter::make('issued_at')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $query, $date) => $query->whereDate('issued_at', '>=', $date))
                            ->when($data['until'], fn (Builder $query, $date) => $query->whereDate('issued_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'view' => Pages\ViewInvoice::route('/{record}'),
        ];
    }
}
