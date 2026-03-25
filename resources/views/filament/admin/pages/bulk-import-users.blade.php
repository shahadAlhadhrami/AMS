<x-filament-panels::page>
    {{-- Download Template --}}
    <div class="mb-6">
        <x-filament::button
            wire:click="downloadTemplate"
            color="gray"
            icon="heroicon-o-arrow-down-tray"
        >
            Download CSV Template
        </x-filament::button>
    </div>

    {{-- Upload Form --}}
    @if (! $imported && ! $showMapping && empty($previewData))
        <div>
            {{ $this->form }}

            <div class="mt-4">
                <x-filament::button wire:click="uploadAndPreview" icon="heroicon-o-eye">
                    Upload & Map Columns
                </x-filament::button>
            </div>
        </div>
    @endif

    {{-- Column Mapping Step --}}
    @if ($showMapping)
        <div class="mt-6 rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-900">
            <h3 class="mb-1 text-lg font-medium text-gray-900 dark:text-white">Map CSV Columns</h3>
            <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                Match each required field to the corresponding column in your CSV file.
            </p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @foreach (['university_id' => 'University ID', 'name' => 'Full Name', 'email' => 'Email Address', 'role' => 'Role'] as $field => $label)
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ $label }} <span class="text-danger-500">*</span>
                        </label>
                        <select
                            wire:model="columnMapping.{{ $field }}"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                            <option value="">— select column —</option>
                            @foreach ($csvHeaders as $header)
                                <option value="{{ $header }}" @selected($columnMapping[$field] === $header)>{{ $header }}</option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
            </div>
            <div class="mt-6 flex gap-3">
                <x-filament::button wire:click="confirmMappingAndPreview" icon="heroicon-o-check">
                    Continue to Preview
                </x-filament::button>
                <x-filament::button wire:click="resetImport" color="gray" icon="heroicon-o-arrow-path">
                    Cancel
                </x-filament::button>
            </div>
        </div>
    @endif

    {{-- Validation Errors --}}
    @if (count($validationErrors) > 0)
        <div class="mt-6 rounded-lg border border-danger-300 bg-danger-50 p-4 dark:border-danger-600 dark:bg-danger-950/20">
            <h3 class="text-sm font-medium text-danger-800 dark:text-danger-200">
                Validation Errors ({{ count($validationErrors) }})
            </h3>
            <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-danger-700 dark:text-danger-300">
                @foreach ($validationErrors as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Preview Table --}}
    @if (count($previewData) > 0 && ! $imported)
        <div class="mt-6">
            <h3 class="mb-3 text-lg font-medium text-gray-900 dark:text-white">
                Preview ({{ count($previewData) }} rows)
            </h3>

            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Row</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">University ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Role</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                        @foreach ($previewData as $row)
                            <tr class="{{ $row['status'] === 'error' ? 'bg-danger-50 dark:bg-danger-950/20' : '' }}">
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $row['row'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $row['university_id'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $row['name'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $row['email'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $row['role'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm">
                                    @if ($row['status'] === 'valid')
                                        <span class="inline-flex items-center rounded-full bg-success-100 px-2.5 py-0.5 text-xs font-medium text-success-800 dark:bg-success-900/20 dark:text-success-400">
                                            Valid
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-danger-100 px-2.5 py-0.5 text-xs font-medium text-danger-800 dark:bg-danger-900/20 dark:text-danger-400">
                                            Error
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Import Button --}}
            <div class="mt-4">
                <x-filament::button
                    wire:click="importUsers"
                    color="success"
                    icon="heroicon-o-check-circle"
                    :disabled="$hasErrors"
                >
                    Import {{ count($previewData) }} Users
                </x-filament::button>
            </div>
        </div>
    @endif

    {{-- Import Success --}}
    @if ($imported)
        <div class="mt-6 rounded-lg border border-success-300 bg-success-50 p-6 dark:border-success-600 dark:bg-success-950/20">
            <h3 class="text-lg font-medium text-success-800 dark:text-success-200">
                Import Successful
            </h3>
            <p class="mt-1 text-sm text-success-700 dark:text-success-300">
                {{ $importedCount }} users have been created successfully.
            </p>
            <div class="mt-4 flex gap-3">
                <x-filament::button
                    wire:click="downloadResults"
                    color="warning"
                    icon="heroicon-o-arrow-down-tray"
                >
                    Download Results CSV (includes passwords)
                </x-filament::button>

                <x-filament::button
                    wire:click="resetImport"
                    color="gray"
                    icon="heroicon-o-arrow-path"
                >
                    Import More
                </x-filament::button>
            </div>
        </div>
    @endif
</x-filament-panels::page>
