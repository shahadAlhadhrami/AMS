<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';

    protected static string | \UnitEnum | null $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        if (! auth()->user()?->hasRole('Super Admin')) {
            return null;
        }

        $count = User::where('is_approved', false)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        if (! auth()->user()?->hasRole('Super Admin')) {
            return null;
        }

        return User::where('is_approved', false)->exists() ? 'danger' : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        if (! auth()->user()?->hasRole('Super Admin')) {
            return null;
        }

        $count = User::where('is_approved', false)->count();

        return $count > 0 ? "{$count} pending coordinator approval(s)" : null;
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('university_id')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->required()
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->confirmed()
                    ->minLength(8),
                Forms\Components\TextInput::make('password_confirmation')
                    ->password()
                    ->requiredWith('password')
                    ->dehydrated(false),
                Forms\Components\Select::make('specialization_id')
                    ->relationship('specialization', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Forms\Components\CheckboxList::make('roles')
                    ->relationship('roles', 'name')
                    ->options(function () {
                        $query = Role::query();

                        if (! auth()->user()->hasRole('Super Admin')) {
                            $query->whereNotIn('name', ['Super Admin', 'Coordinator']);
                        }

                        return $query->pluck('name', 'id');
                    })
                    ->columns(2)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('university_id')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->badge(),
                Tables\Columns\TextColumn::make('specialization.name')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
                Tables\Filters\SelectFilter::make('specialization_id')
                    ->relationship('specialization', 'name')
                    ->label('Specialization'),
            ])
            ->actions([
                Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Coordinator')
                    ->modalDescription('Are you sure you want to approve this coordinator account? They will be able to log in immediately.')
                    ->modalSubmitActionLabel('Yes, Approve')
                    ->visible(fn (User $record): bool => ! $record->is_approved && auth()->user()?->hasRole('Super Admin'))
                    ->action(function (User $record): void {
                        $record->update(['is_approved' => true]);

                        \Filament\Notifications\Notification::make()
                            ->title('Coordinator Approved')
                            ->body("{$record->name} can now log in to the system.")
                            ->success()
                            ->send();
                    }),
                Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Reject & Delete Account')
                    ->modalDescription('This will permanently delete this coordinator\'s registration. This action cannot be undone.')
                    ->modalSubmitActionLabel('Yes, Reject & Delete')
                    ->visible(fn (User $record): bool => ! $record->is_approved && auth()->user()?->hasRole('Super Admin'))
                    ->action(function (User $record): void {
                        $name = $record->name;
                        $record->forceDelete();

                        \Filament\Notifications\Notification::make()
                            ->title('Registration Rejected')
                            ->body("{$name}'s registration has been removed.")
                            ->danger()
                            ->send();
                    }),
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->check() && ! auth()->user()->hasRole('Super Admin')) {
            $query->where('is_approved', true);
        }

        return $query;
    }
}
