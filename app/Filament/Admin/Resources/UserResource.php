<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Concerns\HidesDuringMasterDataSetup;
use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\User;
use App\Support\FilamentLookupCache;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Unique;
use Livewire\Component;

class UserResource extends Resource
{
    use HidesDuringMasterDataSetup;

    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|\UnitEnum|null $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        if (! auth()->user()?->hasRole('Super Admin')) {
            return null;
        }

        $count = static::pendingApprovalCount();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        if (! auth()->user()?->hasRole('Super Admin')) {
            return null;
        }

        return static::pendingApprovalCount() > 0 ? 'danger' : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        if (! auth()->user()?->hasRole('Super Admin')) {
            return null;
        }

        $count = static::pendingApprovalCount();

        return $count > 0 ? "{$count} pending coordinator approval(s)" : null;
    }

    public static function pendingApprovalCount(): int
    {
        return FilamentLookupCache::pendingCoordinatorApprovals();
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('university_id')
                    ->required()
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: fn (Unique $rule): Unique => $rule->whereNull('deleted_at'),
                    )
                    ->maxLength(255),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->required()
                    ->email()
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: fn (Unique $rule): Unique => $rule->whereNull('deleted_at'),
                    )
                    ->maxLength(255),
                Forms\Components\Select::make('specialization_id')
                    ->options(fn (): array => FilamentLookupCache::specializationOptions())
                    ->searchable()
                    ->preload()
                    ->nullable(),
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
                Forms\Components\CheckboxList::make('roles')
                    ->relationship('roles', 'name')
                    ->options(fn (): array => FilamentLookupCache::roleOptions(auth()->user()->hasRole('Super Admin')))
                    ->columns(2)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginationMode(PaginationMode::Simple)
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
                    ->options(fn (): array => FilamentLookupCache::roleOptions())
                    ->multiple()
                    ->preload()
                    ->query(function (Builder $query, array $data): Builder {
                        $roleIds = array_filter($data['values'] ?? []);

                        return empty($roleIds)
                            ? $query
                            : $query->whereHas('roles', fn (Builder $query): Builder => $query->whereKey($roleIds));
                    }),
                Tables\Filters\SelectFilter::make('specialization_id')
                    ->options(fn (): array => FilamentLookupCache::specializationOptions())
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
                    ->action(function (User $record, Component $livewire): void {
                        $record->update(['is_approved' => true]);
                        static::refreshPendingApprovalBadges($livewire);

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
                    ->action(function (User $record, Component $livewire): void {
                        $name = $record->name;
                        $record->forceDelete();
                        static::refreshPendingApprovalBadges($livewire);

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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['roles', 'specialization']);

        if (auth()->check() && ! auth()->user()->hasRole('Super Admin')) {
            $query->approved();
        }

        return $query;
    }

    protected static function refreshPendingApprovalBadges(Component $livewire): void
    {
        FilamentLookupCache::forgetPendingCoordinatorApprovals();

        if ($livewire instanceof Pages\ListUsers) {
            $livewire->refreshPendingApprovalTabs();
        }

        $livewire->dispatch('refresh-sidebar');
        $livewire->dispatch('refresh-topbar');
    }
}
