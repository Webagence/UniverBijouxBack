<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\Invoice;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    protected static ?string $title = 'Factures';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('invoice_number')
                    ->label('Numéro de facture')
                    ->required(),
                Forms\Components\DateTimePicker::make('issued_at')
                    ->label('Date d\'émission')
                    ->default(now())
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('invoice_number')
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('N° facture')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('total_ht')
                    ->label('Total HT')
                    ->money('EUR'),
                Tables\Columns\TextColumn::make('vat_amount')
                    ->label('TVA')
                    ->money('EUR'),
                Tables\Columns\TextColumn::make('total_ttc')
                    ->label('Total TTC')
                    ->money('EUR')
                    ->color('success'),
                Tables\Columns\TextColumn::make('issued_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->defaultSort('issued_at', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Créer une facture')
                    ->mutateFormDataUsing(function (array $data): array {
                        $order = $this->getOwnerRecord();
                        $data['order_id'] = $order->id;
                        $data['user_id'] = $order->user_id;
                        $data['total_ht'] = $order->subtotal_ht;
                        $data['vat_amount'] = $order->vat_amount;
                        $data['total_ttc'] = $order->total_ttc;

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }
}
