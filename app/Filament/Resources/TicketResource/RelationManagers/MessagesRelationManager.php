<?php

namespace App\Filament\Resources\TicketResource\RelationManagers;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class MessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    protected static ?string $recordTitleAttribute = 'body';

    protected static ?string $title = 'Messages';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('body')
                    ->label('Message')
                    ->required()
                    ->rows(4),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('body')
            ->columns([
                Tables\Columns\TextColumn::make('body')
                    ->label('Message')
                    ->limit(80)
                    ->wrap(),
                Tables\Columns\TextColumn::make('author.name')
                    ->label('Auteur')
                    ->badge()
                    ->color(fn (Model $record) => $record->is_admin ? 'primary' : 'gray'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'asc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Répondre')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['author_id'] = auth()->id();
                        $data['is_admin'] = true;

                        $ticket = $this->getOwnerRecord();
                        if ($ticket->status === Ticket::STATUS_OPEN) {
                            $ticket->update(['status' => Ticket::STATUS_PENDING]);
                        }

                        return $data;
                    })
                    ->successNotificationTitle('Réponse envoyée'),
            ])
            ->actions([
                // No edit/delete actions for messages
            ])
            ->bulkActions([]);
    }
}
