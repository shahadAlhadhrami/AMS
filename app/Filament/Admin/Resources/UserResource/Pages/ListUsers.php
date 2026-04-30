<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Pages\BulkImportUsers;
use App\Filament\Admin\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('importCsv')
                ->label('Import CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->url(BulkImportUsers::getUrl()),
        ];
    }

    public function getTabs(): array
    {
        if (! auth()->user()?->hasRole('Super Admin')) {
            return [];
        }

        $pendingCount = User::where('is_approved', false)->count();

        return [
            'all' => Tab::make('All Users')
                ->icon('heroicon-o-users'),

            'pending' => Tab::make('Pending Approvals')
                ->icon('heroicon-o-clock')
                ->badge($pendingCount ?: null)
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_approved', false)),

        ];
    }
}
