<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\InvoicesRelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\OrderItemsRelationManager;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Commandes';

    protected static ?string $navigationGroup = 'Ventes';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations')
                    ->schema([
                        Forms\Components\TextInput::make('reference')
                            ->label('Référence')
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\Select::make('user_id')
                            ->label('Client')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
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
                            ->default(Order::STATUS_PENDING)
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Montants')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal_ht')
                            ->label('Sous-total HT')
                            ->numeric()
                            ->prefix('€')
                            ->readonly()
                            ->dehydrated(),
                        Forms\Components\TextInput::make('vat_amount')
                            ->label('TVA')
                            ->numeric()
                            ->prefix('€')
                            ->readonly()
                            ->dehydrated(),
                        Forms\Components\TextInput::make('shipping_ht')
                            ->label('Frais de livraison HT')
                            ->numeric()
                            ->prefix('€')
                            ->default(0),
                        Forms\Components\TextInput::make('total_ttc')
                            ->label('Total TTC')
                            ->numeric()
                            ->prefix('€')
                            ->readonly()
                            ->dehydrated(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Livraison')
                    ->schema([
                        Forms\Components\TextInput::make('carrier')
                            ->label('Transporteur'),
                        Forms\Components\TextInput::make('tracking_number')
                            ->label('N° de suivi'),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->columnSpanFull()
                            ->rows(3),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Adresse de livraison')
                    ->schema([
                        Forms\Components\ViewField::make('shipping_address')
                            ->view('filament.forms.components.shipping-address-view')
                            ->hiddenOn('create'),
                    ])
                    ->hiddenOn('create'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable()
                    ->copyable()
                    ->description(fn (Order $record) => $record->created_at->format('d/m/Y H:i')),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Client')
                    ->searchable()
                    ->description(fn (Order $record) => $record->user->email),
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
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Articles')
                    ->counts('items'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        Order::STATUS_PENDING => 'En attente',
                        Order::STATUS_CONFIRMED => 'Confirmée',
                        Order::STATUS_PREPARING => 'En préparation',
                        Order::STATUS_SHIPPED => 'Expédiée',
                        Order::STATUS_DELIVERED => 'Livrée',
                        Order::STATUS_CANCELLED => 'Annulée',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'], fn (Builder $query, $date) => $query->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn (Builder $query, $date) => $query->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('generateInvoice')
                    ->label('Facturer')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Générer la facture')
                    ->modalDescription('Une facture sera créée pour cette commande.')
                    ->modalSubmitActionLabel('Générer')
                    ->action(function (Order $record) {
                        $existing = $record->invoices()->first();
                        if ($existing) {
                            Notification::make()
                                ->title('Facture existante')
                                ->body("La commande {$record->reference} a déjà une facture ({$existing->invoice_number}).")
                                ->warning()
                                ->send();
                            return;
                        }

                        $invoiceNumber = 'FAC-' . now()->format('Ymd') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);

                        Invoice::create([
                            'invoice_number' => $invoiceNumber,
                            'order_id' => $record->id,
                            'user_id' => $record->user_id,
                            'total_ht' => $record->subtotal_ht,
                            'vat_amount' => $record->vat_amount,
                            'total_ttc' => $record->total_ttc,
                            'issued_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Facture générée')
                            ->body("Facture {$invoiceNumber} créée pour la commande {$record->reference}.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Order $record): bool => $record->status !== Order::STATUS_CANCELLED && $record->invoices()->count() === 0),
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
        return [
            OrderItemsRelationManager::class,
            InvoicesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
