<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\Role;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Comptes pros';

    protected static ?string $navigationGroup = 'Utilisateurs';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(User::class, 'email', ignoreRecord: true),
                        Forms\Components\TextInput::make('password')
                            ->label('Mot de passe')
                            ->password()
                            ->revealable()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->dehydrated(fn ($state): bool => filled($state))
                            ->dehydrateStateUsing(fn ($state): string => Hash::make($state))
                            ->minLength(8),
                        Forms\Components\TextInput::make('phone')
                            ->label('Téléphone')
                            ->tel(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Statut & Rôles')
                    ->schema([
                        Forms\Components\Toggle::make('approved')
                            ->label('Compte approuvé')
                            ->default(false)
                            ->helperText('Le client peut commander uniquement si son compte est approuvé'),
                        Forms\Components\CheckboxList::make('roles')
                            ->label('Rôles')
                            ->relationship('roles', 'name')
                            ->options(Role::pluck('label', 'id'))
                            ->descriptions(Role::all()->pluck('name', 'id')->map(fn ($name) => match ($name) {
                                'admin' => 'Accès complet au panel admin',
                                'pro' => 'Client professionnel B2B',
                                default => '',
                            }))
                            ->columns(2),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Rôles')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'pro' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin' => 'Admin',
                        'pro' => 'Pro',
                        default => $state,
                    })
                    ->limitList(2),
                Tables\Columns\IconColumn::make('approved')
                    ->label('Approuvé')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Commandes')
                    ->counts('orders'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Inscrit le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('approved')
                    ->label('Compte approuvé'),
                Tables\Filters\Filter::make('has_role')
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['value'], fn ($q, $role) => $q->whereHas('roles', fn ($q) => $q->where('name', $role)));
                    })
                    ->form([
                        Forms\Components\Select::make('value')
                            ->label('Rôle')
                            ->options(Role::pluck('name', 'name')),
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Approuver')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (User $record): bool => !$record->approved)
                    ->action(function (User $record) {
                        $record->update(['approved' => true]);
                        Notification::make()
                            ->title('Compte approuvé')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('approve_selected')
                        ->label('Approuver la sélection')
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['approved' => true]);
                            }
                            Notification::make()
                                ->title(count($records) . ' compte(s) approuvé(s)')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
