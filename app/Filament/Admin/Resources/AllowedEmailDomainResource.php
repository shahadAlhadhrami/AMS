<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AllowedEmailDomainResource\Pages;
use App\Models\AllowedEmailDomain;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;

class AllowedEmailDomainResource extends Resource
{
    protected static ?string $model = AllowedEmailDomain::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-at-symbol';

    protected static string | \UnitEnum | null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Allowed Email Domains';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('domain')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->placeholder('e.g. university.edu')
                    ->helperText('Enter the domain without the @ symbol')
                    ->rules(['regex:/^[a-zA-Z0-9][a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/']),
                Forms\Components\Toggle::make('is_active')
                    ->default(true)
                    ->label('Active'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('domain')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn (string $state) => '@' . $state),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
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
            'index' => Pages\ListAllowedEmailDomains::route('/'),
            'create' => Pages\CreateAllowedEmailDomain::route('/create'),
            'edit' => Pages\EditAllowedEmailDomain::route('/{record}/edit'),
        ];
    }
}
