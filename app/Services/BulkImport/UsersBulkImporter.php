<?php

namespace App\Services\BulkImport;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UsersBulkImporter implements BulkImporter
{
    use ResolvesCsvFilePath;

    public function key(): string
    {
        return 'users';
    }

    public function label(): string
    {
        return 'Users';
    }

    public function description(): string
    {
        return 'Create student, supervisor, and reviewer accounts in bulk. Generated passwords are returned in a results CSV.';
    }

    public function requiresColumnMapping(): bool
    {
        return true;
    }

    public function supportsMultiFile(): bool
    {
        return false;
    }

    public function extraFormFields(): array
    {
        return [];
    }

    public function contextFormFields(): array
    {
        return [];
    }

    public function validateContext(array $previewData, array $context): array
    {
        return ['errors' => [], 'hasErrors' => false];
    }

    public function systemFields(): array
    {
        return ['university_id', 'name', 'email', 'role'];
    }

    public function systemFieldLabels(): array
    {
        return [
            'university_id' => 'University ID',
            'name'          => 'Full Name',
            'email'         => 'Email Address',
            'role'          => 'Role',
        ];
    }

    public function downloadTemplate(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['university_id', 'name', 'email', 'role']);
            fputcsv($file, ['26s2020', 'Hamed Al-Balushi', '26s2020@utas.edu.om', 'Student']);
            fputcsv($file, ['26j2174', 'Aisha Al-Harthi', '26j2174@utas.edu.om', 'Student']);
            fputcsv($file, ['e4382', 'Ahmed Al-Balushi', 'ahmed.al-balushi@utas.edu.om', 'Supervisor']);
            fputcsv($file, ['e7051', 'Nawal Al-Kharusi', 'nawal.al-kharusi@utas.edu.om', 'Reviewer']);
            fputcsv($file, ['e1926', 'Salim Al-Harthy', 'salim.al-harthy@utas.edu.om', 'Reviewer/Supervisor']);
            fclose($file);
        }, 'users_import_template.csv');
    }

    public function validateRows(array $files, array $columnMapping, array $context): array
    {
        $previewData = [];
        $errors = [];

        $csvPath = $files[0] ?? null;
        $filePath = $this->resolveCsvFilePath($csvPath);

        if (! $filePath) {
            return [
                'previewData' => [],
                'previewColumns' => $this->previewColumns(),
                'errors' => ['CSV file not found. Please re-upload.'],
                'hasErrors' => true,
            ];
        }

        try {
            $parsed = SpreadsheetReader::read($filePath);
        } catch (\Throwable $e) {
            @unlink($filePath);
            return [
                'previewData' => [],
                'previewColumns' => $this->previewColumns(),
                'errors' => ['Unable to read the spreadsheet: ' . $e->getMessage()],
                'hasErrors' => true,
            ];
        }

        @unlink($filePath);

        $rawHeaders = $parsed['headers'];
        $rows = [];
        $rowNumber = 1; // header is row 1
        foreach ($parsed['rows'] as $rawCells) {
            $rowNumber++;
            $rawRow = array_combine($rawHeaders, $rawCells);

            $rows[] = [
                'university_id' => trim($rawRow[$columnMapping['university_id']] ?? ''),
                'name'          => trim($rawRow[$columnMapping['name']] ?? ''),
                'email'         => trim($rawRow[$columnMapping['email']] ?? ''),
                'role'          => trim($rawRow[$columnMapping['role']] ?? ''),
                '_row'          => $rowNumber,
            ];
        }

        if (empty($rows)) {
            return [
                'previewData' => [],
                'previewColumns' => $this->previewColumns(),
                'errors' => ['CSV file contains no data rows.'],
                'hasErrors' => true,
            ];
        }

        $validRoles = Role::pluck('name')
            ->mapWithKeys(fn ($name) => [Str::lower($name) => $name])
            ->toArray();
        $restrictedRoles = ['super admin', 'coordinator'];

        $seenUniversityIds = [];
        $seenEmails = [];
        $hasErrors = false;

        foreach ($rows as $row) {
            $rowNum = $row['_row'];
            $rowErrors = [];

            $universityId = trim($row['university_id'] ?? '');
            if (empty($universityId)) {
                $rowErrors[] = 'university_id is required';
            } else {
                if (in_array($universityId, $seenUniversityIds, true)) {
                    $rowErrors[] = "Duplicate university_id '{$universityId}' in CSV";
                } else {
                    $seenUniversityIds[] = $universityId;
                }

                if (User::where('university_id', $universityId)->exists()) {
                    $rowErrors[] = "university_id '{$universityId}' already exists in the system";
                }
            }

            $name = trim($row['name'] ?? '');
            if (empty($name)) {
                $rowErrors[] = 'name is required';
            }

            $email = trim($row['email'] ?? '');
            if (empty($email)) {
                $rowErrors[] = 'email is required';
            } else {
                $emailValidator = Validator::make(['email' => $email], ['email' => 'email']);
                if ($emailValidator->fails()) {
                    $rowErrors[] = "'{$email}' is not a valid email address";
                } else {
                    if (in_array(strtolower($email), $seenEmails, true)) {
                        $rowErrors[] = "Duplicate email '{$email}' in CSV";
                    } else {
                        $seenEmails[] = strtolower($email);
                    }

                    if (User::where('email', $email)->exists()) {
                        $rowErrors[] = "Email '{$email}' already exists in the system";
                    }
                }
            }

            $role = trim($row['role'] ?? '');
            if (empty($role)) {
                $rowErrors[] = 'role is required';
            } else {
                $resolvedRole = $this->resolveImportRole($role, $validRoles);

                if (! $resolvedRole) {
                    $rowErrors[] = "Role '{$role}' is not valid";
                } elseif (in_array(Str::lower($resolvedRole), $restrictedRoles, true)) {
                    $rowErrors[] = "Role '{$role}' cannot be assigned via bulk import";
                } else {
                    $role = $resolvedRole;
                }
            }

            $status = empty($rowErrors) ? 'valid' : 'error';

            $previewData[] = [
                'row' => $rowNum,
                'university_id' => $universityId,
                'name' => $name,
                'email' => $email,
                'role' => $role,
                'status' => $status,
                'errors' => $rowErrors,
            ];

            if (! empty($rowErrors)) {
                $hasErrors = true;
                foreach ($rowErrors as $error) {
                    $errors[] = "Row {$rowNum}: {$error}";
                }
            }
        }

        return [
            'previewData' => $previewData,
            'previewColumns' => $this->previewColumns(),
            'errors' => $errors,
            'hasErrors' => $hasErrors,
        ];
    }

    public function import(array $previewData, array $context): array
    {
        $results = [];

        foreach ($previewData as $row) {
            $password = Str::random(12);

            $user = User::create([
                'university_id' => $row['university_id'],
                'name' => $row['name'],
                'email' => $row['email'],
                'password' => $password,
            ]);

            $role = Role::whereRaw('LOWER(name) = LOWER(?)', [$row['role']])->first();
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

        return ['count' => count($results), 'results' => $results];
    }

    public function hasResultsDownload(): bool
    {
        return true;
    }

    public function downloadResults(array $results): StreamedResponse
    {
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

    protected function previewColumns(): array
    {
        return [
            'university_id' => 'University ID',
            'name' => 'Name',
            'email' => 'Email',
            'role' => 'Role',
        ];
    }

    protected function resolveImportRole(string $role, array $validRoles): ?string
    {
        $normalisedRole = preg_replace('/\s*\/\s*/', '/', Str::lower(trim($role)));

        $aliases = [
            'reviewer' => 'Reviewer/Supervisor',
            'supervisor' => 'Reviewer/Supervisor',
            'reviewer/supervisor' => 'Reviewer/Supervisor',
            'supervisor/reviewer' => 'Reviewer/Supervisor',
        ];

        $role = $aliases[$normalisedRole] ?? trim($role);

        return $validRoles[Str::lower($role)] ?? null;
    }
}
