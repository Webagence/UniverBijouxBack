<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Filament\Resources\TicketResource\RelationManagers\MessagesRelationManager;
use App\Models\Ticket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'Tickets SAV';

    protected static ?string $navigationGroup = 'Support';

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
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('subject')
                            ->label('Sujet')
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                Ticket::STATUS_OPEN => 'Ouvert',
                                Ticket::STATUS_PENDING => 'En attente',
                                Ticket::STATUS_RESOLVED => 'Résolu',
                                Ticket::STATUS_CLOSED => 'Fermé',
                            ])
                            ->required(),
                        Forms\Components\Select::make('priority')
                            ->label('Priorité')
                            ->options([
                                'low' => 'Basse',
                                'normal' => 'Normale',
                                'high' => 'Haute',
                                'urgent' => 'Urgente',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('user.email')
                            ->label('Client')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('order.reference')
                            ->label('Commande liée')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Client')
                    ->searchable(),
                Tables\Columns\TextColumn::make('order.reference')
                    ->label('Commande')
                    ->placeholder('—'),
                Tables\Columns\BadgeColumn::make('status')
                    ->searchable()
                    ->colors([
                        'success' => 'open',
                        'warning' => 'pending',
                        'info' => 'resolved',
                        'gray' => 'closed',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'open' => 'Ouvert',
                        'pending' => 'En attente',
                        'resolved' => 'Résolu',
                        'closed' => 'Fermé',
                        default => $state,
                    }),
                Tables\Columns\BadgeColumn::make('priority')
                    ->searchable()
                    ->colors([
                        'gray' => 'low',
                        'primary' => 'normal',
                        'danger' => 'high',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'low' => 'Basse',
                        'normal' => 'Normale',
                        'high' => 'Haute',
                        'urgent' => 'Urgente',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        Ticket::STATUS_OPEN => 'Ouvert',
                        Ticket::STATUS_PENDING => 'En attente',
                        Ticket::STATUS_RESOLVED => 'Résolu',
                        Ticket::STATUS_CLOSED => 'Fermé',
                    ]),
                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'low' => 'Basse',
                        'normal' => 'Normale',
                        'high' => 'Haute',
                        'urgent' => 'Urgente',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('close')
                        ->label('Fermer la sélection')
                        ->action(fn ($records) => $records->each->update(['status' => Ticket::STATUS_CLOSED])),
                    Tables\Actions\BulkAction::make('resolve')
                        ->label('Résoudre la sélection')
                        ->action(fn ($records) => $records->each->update(['status' => Ticket::STATUS_RESOLVED])),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            MessagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'view' => Pages\ViewTicket::route('/{record}'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }
}
