<?php

namespace App\Filament\Admin\Resources\RubricTemplateResource\Pages;

use App\Filament\Admin\Pages\BulkImports;
use App\Filament\Admin\Resources\RubricTemplateResource;
use App\Models\RubricFolder;
use App\Models\RubricTemplate;
use App\Support\FilamentLookupCache;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class ListRubricTemplates extends ListRecords
{
    protected static string $resource = RubricTemplateResource::class;

    protected string $view = 'filament.admin.resources.rubric-template-resource.pages.list-rubric-templates';

    #[Url]
    public ?int $currentFolderId = null;

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->where('rubric_folder_id', $this->currentFolderId);
    }

    public function navigateToFolder(?int $folderId): void
    {
        $this->currentFolderId = $folderId;
        $this->resetTable();
    }

    public function getCurrentFolder(): ?RubricFolder
    {
        if ($this->currentFolderId === null) {
            return null;
        }

        return RubricFolder::find($this->currentFolderId);
    }

    public function getBreadcrumbs(): array
    {
        if ($this->currentFolderId === null) {
            return [];
        }

        $folder = $this->getCurrentFolder();
        if (! $folder) {
            return [];
        }

        $breadcrumbs = $folder->getAncestors()
            ->map(fn (RubricFolder $f) => ['id' => $f->id, 'name' => $f->name])
            ->toArray();

        $breadcrumbs[] = ['id' => $folder->id, 'name' => $folder->name];

        return $breadcrumbs;
    }

    public function getSubfolders(): \Illuminate\Database\Eloquent\Collection
    {
        return RubricFolder::where('parent_id', $this->currentFolderId)
            ->orderBy('name')
            ->with('creator')
            ->get();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('createFolder')
                ->label('New Folder')
                ->icon('heroicon-o-folder-plus')
                ->color('gray')
                ->form([
                    Forms\Components\TextInput::make('name')
                        ->label('Folder Name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('e.g., Phase I Templates'),
                ])
                ->action(function (array $data): void {
                    RubricFolder::create([
                        'name' => $data['name'],
                        'parent_id' => $this->currentFolderId,
                        'created_by' => auth()->id(),
                    ]);
                    FilamentLookupCache::forgetRubricFolders();

                    Notification::make()
                        ->title("Folder '{$data['name']}' created.")
                        ->success()
                        ->send();
                }),
            Actions\Action::make('renameFolder')
                ->label('Rename Folder')
                ->icon('heroicon-o-pencil')
                ->color('gray')
                ->visible(fn (): bool => $this->currentFolderId !== null && $this->getCurrentFolder()?->created_by === auth()->id())
                ->form(fn (): array => [
                    Forms\Components\TextInput::make('name')
                        ->label('New Name')
                        ->required()
                        ->maxLength(255)
                        ->default($this->getCurrentFolder()?->name),
                ])
                ->action(function (array $data): void {
                    $folder = $this->getCurrentFolder();
                    if (! $folder) {
                        return;
                    }

                    $folder->update(['name' => $data['name']]);
                    FilamentLookupCache::forgetRubricFolders();

                    Notification::make()
                        ->title("Folder renamed to '{$data['name']}'.")
                        ->success()
                        ->send();
                }),
            Actions\Action::make('deleteFolder')
                ->label('Delete Folder')
                ->icon('heroicon-o-folder-minus')
                ->color('danger')
                ->visible(fn (): bool => $this->currentFolderId !== null && $this->getCurrentFolder()?->created_by === auth()->id())
                ->requiresConfirmation()
                ->modalHeading('Delete Folder')
                ->modalDescription('This will only delete the folder itself. Templates and subfolders inside will be moved to "No folder". Are you sure?')
                ->action(function (): void {
                    $folder = $this->getCurrentFolder();
                    if (! $folder) {
                        return;
                    }

                    // Move contents to parent before deleting
                    RubricTemplate::where('rubric_folder_id', $folder->id)
                        ->update(['rubric_folder_id' => $folder->parent_id]);
                    RubricFolder::where('parent_id', $folder->id)
                        ->update(['parent_id' => $folder->parent_id]);

                    $parentId = $folder->parent_id;
                    $folder->delete();
                    FilamentLookupCache::forgetRubricFolders();

                    $this->navigateToFolder($parentId);

                    Notification::make()
                        ->title('Folder deleted.')
                        ->success()
                        ->send();
                }),
            Actions\Action::make('importCsv')
                ->label('Import from CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->url(BulkImports::getUrl(['type' => 'rubric-templates'])),
        ];
    }
}
