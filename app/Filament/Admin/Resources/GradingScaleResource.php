<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\GradingScaleResource\Pages;
use App\Models\GradingScale;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;

class GradingScaleResource extends Resource
{
    protected static ?string $model = GradingScale::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string | \UnitEnum | null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('letter_grade')
                    ->required()
                    ->maxLength(10),
                Forms\Components\TextInput::make('min_score')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->rules([
                        fn (Get $get, ?GradingScale $record): \Closure =>
                            function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                                $min = (float) $value;
                                $max = (float) $get('max_score');

                                if (! $max) {
                                    return;
                                }

                                if ($min >= $max) {
                                    $fail('Min score must be less than max score.');
                                    return;
                                }

                                $overlap = GradingScale::query()
                                    ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                                    ->where('min_score', '<=', $max)
                                    ->where('max_score', '>=', $min)
                                    ->exists();

                                if ($overlap) {
                                    $fail('This score range overlaps with an existing grading scale entry.');
                                }
                            },
                    ]),
                Forms\Components\TextInput::make('max_score')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->gt('min_score'),
                Forms\Components\TextInput::make('gpa_equivalent')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(4.00),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('min_score', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('letter_grade')
                    ->sortable(),
                Tables\Columns\TextColumn::make('min_score')
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_score')
                    ->sortable(),
                Tables\Columns\TextColumn::make('gpa_equivalent')
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
            'index' => Pages\ListGradingScales::route('/'),
            'create' => Pages\CreateGradingScale::route('/create'),
            'edit' => Pages\EditGradingScale::route('/{record}/edit'),
        ];
    }
}
