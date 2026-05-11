<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Pages\BulkImports;
use App\Filament\Admin\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
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
                ->url(BulkImports::getUrl(['type' => 'users'])),
        ];
    }

    public function getTabs(): array
    {
        if (! auth()->user()?->hasRole('Super Admin')) {
            return [];
        }

        $pendingCount = UserResource::pendingApprovalCount();

        return [
            'all' => Tab::make('All Users')
                ->icon('heroicon-o-users'),

            'pending' => Tab::make('Pending Approvals')
                ->icon('heroicon-o-clock')
                ->badge($pendingCount > 0 ? $pendingCount : null)
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->unapproved()),

        ];
    }

    public function refreshPendingApprovalTabs(): void
    {
        unset($this->cachedTabs);
    }
}
