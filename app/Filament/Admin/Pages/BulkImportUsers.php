<?php

namespace App\Filament\Admin\Pages;

use App\Models\User;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BulkImportUsers extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static string | \UnitEnum | null $navigationGroup = 'Tools';

    protected static ?string $navigationLabel = 'Bulk Import';

    protected static ?string $title = 'Bulk Import Users';

    protected string $view = 'filament.admin.pages.bulk-import-users';

    public array $data = [];

    public array $previewData = [];

    public array $validationErrors = [];

    public bool $hasErrors = false;

    public bool $imported = false;

    public int $importedCount = 0;

    public array $importResults = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('csvPath')
                    ->label('CSV File')
                    ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                    ->required()
                    ->disk('local')
                    ->directory('csv-imports')
                    ->visibility('private'),
            ])
            ->statePath('data');
    }

    public function uploadAndPreview(): void
    {
        \Log::info('BulkImport: uploadAndPreview called', ['data' => $this->data]);
        // getState() validates AND processes Filament file uploads (saves temp → final path)
        $formState = $this->form->getState();
        \Log::info('BulkImport uploadAndPreview', ['formState' => $formState, 'data' => $this->data]);
        $this->imported = false;
        $this->importResults = [];
        $this->previewData = [];
        $this->validationErrors = [];
        $this->hasErrors = false;

        $csvPath = $formState['csvPath'] ?? null;

        if (! $csvPath) {
            Notification::make()
                ->title('No CSV file uploaded.')
                ->danger()
                ->send();

            return;
        }

        $filePath = storage_path('app/private/' . $csvPath);

        if (! file_exists($filePath)) {
            // Also try without 'private/' prefix (depending on Filament version)
            $filePath = storage_path('app/' . $csvPath);
        }

        if (! file_exists($filePath)) {
            Notification::make()
                ->title('CSV file not found.')
                ->danger()
                ->send();

            return;
        }

        $handle = fopen($filePath, 'r');

        if (! $handle) {
            Notification::make()
                ->title('Unable to read the CSV file.')
                ->danger()
                ->send();

            return;
        }

        $headers = fgetcsv($handle, length: 0, escape: '');

        if (! $headers) {
            fclose($handle);
            Notification::make()
                ->title('CSV file is empty or has no headers.')
                ->danger()
                ->send();

            return;
        }

        $headers = array_map('trim', array_map('strtolower', $headers));
        $requiredHeaders = ['university_id', 'name', 'email', 'role'];

        $missingHeaders = array_diff($requiredHeaders, $headers);

        if (! empty($missingHeaders)) {
            fclose($handle);
            Notification::make()
                ->title('Missing required columns: ' . implode(', ', $missingHeaders))
                ->danger()
                ->send();

            return;
        }

        $rows = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle, length: 0, escape: '')) !== false) {
            $rowNumber++;
            $rowData = array_combine($headers, array_pad($row, count($headers), ''));
            $rowData['_row'] = $rowNumber;
            $rows[] = $rowData;
        }

        fclose($handle);

        if (empty($rows)) {
            Notification::make()
                ->title('CSV file contains no data rows.')
                ->warning()
                ->send();

            return;
        }

        $this->validateRows($rows);

        // Clean up the uploaded file
        @unlink($filePath);
    }

    protected function validateRows(array $rows): void
    {
        $validRoles = Role::pluck('name')->map(fn ($name) => strtolower($name))->toArray();
        $isSuperAdmin = auth()->user()->hasRole('Super Admin');
        $restrictedRoles = ['super admin', 'coordinator'];

        $seenUniversityIds = [];
        $seenEmails = [];

        foreach ($rows as $row) {
            $rowNumber = $row['_row'];
            $rowErrors = [];

            // Validate university_id
            $universityId = trim($row['university_id'] ?? '');

            if (empty($universityId)) {
                $rowErrors[] = 'university_id is required';
            } else {
                if (in_array($universityId, $seenUniversityIds)) {
                    $rowErrors[] = "Duplicate university_id '{$universityId}' in CSV";
                } else {
                    $seenUniversityIds[] = $universityId;
                }

                if (User::where('university_id', $universityId)->exists()) {
                    $rowErrors[] = "university_id '{$universityId}' already exists in the system";
                }
            }

            // Validate name
            $name = trim($row['name'] ?? '');

            if (empty($name)) {
                $rowErrors[] = 'name is required';
            }

            // Validate email
            $email = trim($row['email'] ?? '');

            if (empty($email)) {
                $rowErrors[] = 'email is required';
            } else {
                $emailValidator = Validator::make(['email' => $email], ['email' => 'email']);

                if ($emailValidator->fails()) {
                    $rowErrors[] = "'{$email}' is not a valid email address";
                } else {
                    if (in_array(strtolower($email), $seenEmails)) {
                        $rowErrors[] = "Duplicate email '{$email}' in CSV";
                    } else {
                        $seenEmails[] = strtolower($email);
                    }

                    if (User::where('email', $email)->exists()) {
                        $rowErrors[] = "Email '{$email}' already exists in the system";
                    }
                }
            }

            // Validate role
            $role = trim($row['role'] ?? '');

            if (empty($role)) {
                $rowErrors[] = 'role is required';
            } else {
                if (! in_array(strtolower($role), $validRoles)) {
                    $rowErrors[] = "Role '{$role}' is not valid";
                } elseif (! $isSuperAdmin && in_array(strtolower($role), $restrictedRoles)) {
                    $rowErrors[] = "You do not have permission to assign the '{$role}' role";
                }
            }

            $status = empty($rowErrors) ? 'valid' : 'error';

            $this->previewData[] = [
                'row' => $rowNumber,
                'university_id' => $universityId,
                'name' => $name,
                'email' => $email,
                'role' => $role,
                'status' => $status,
                'errors' => $rowErrors,
            ];

            if (! empty($rowErrors)) {
                $this->hasErrors = true;

                foreach ($rowErrors as $error) {
                    $this->validationErrors[] = "Row {$rowNumber}: {$error}";
                }
            }
        }
    }

    public function importUsers(): void
    {
        if ($this->hasErrors || empty($this->previewData)) {
            Notification::make()
                ->title('Cannot import: fix validation errors first.')
                ->danger()
                ->send();

            return;
        }

        $results = [];

        DB::beginTransaction();

        try {
            foreach ($this->previewData as $row) {
                $password = Str::random(12);

                $user = User::create([
                    'university_id' => $row['university_id'],
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'password' => $password,
                ]);

                // Find the role with proper casing from the database
                $role = Role::whereRaw('LOWER(name) = ?', [strtolower($row['role'])])->first();

                if ($role) {
                    $user->assignRole($role);
                }

                $results[] = [
                    'university_id' => $row['university_id'],
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'role' => $role?->name ?? $row['role'],
                    'password' => $password,
                ];
            }

            DB::commit();

            $this->imported = true;
            $this->importedCount = count($results);
            $this->importResults = $results;

            Notification::make()
                ->title("Successfully imported {$this->importedCount} users.")
                ->success()
                ->send();
        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Import failed: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function downloadTemplate(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['university_id', 'name', 'email', 'role']);
            fputcsv($file, ['IT001234', 'Ali Al-Busaidi', 'ali@example.edu', 'Supervisor']);
            fputcsv($file, ['IT001235', 'Sara Al-Habsi', 'sara@example.edu', 'Reviewer']);
            fputcsv($file, ['IT001236', 'Mohammed Al-Sadi', 'mohammed@example.edu', 'Student']);
            fclose($file);
        }, 'users_import_template.csv');
    }

    public function downloadResults(): StreamedResponse
    {
        $results = $this->importResults;

        return response()->streamDownload(function () use ($results) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['university_id', 'name', 'email', 'role', 'password']);

            foreach ($results as $row) {
                fputcsv($file, [
                    $row['university_id'],
                    $row['name'],
                    $row['email'],
                    $row['role'],
                    $row['password'],
                ]);
            }

            fclose($file);
        }, 'import_results_' . now()->format('Y-m-d_His') . '.csv');
    }

    public function resetImport(): void
    {
        $this->data = [];
        $this->form->fill();
        $this->previewData = [];
        $this->validationErrors = [];
        $this->hasErrors = false;
        $this->imported = false;
        $this->importedCount = 0;
        $this->importResults = [];
    }
}
