<div class="space-y-4">
    @if ($this->projectImportShowMapping)
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
            <h3 class="text-base font-medium text-gray-900 dark:text-white">Map Import Columns</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Match each required project field to a column from the uploaded file.
            </p>

            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                @foreach ($this->getProjectImportFieldLabels() as $field => $label)
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ $label }} <span class="text-danger-500">*</span>
                        </label>
                        <select
                            wire:model="projectImportColumnMapping.{{ $field }}"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                            <option value="">Select column</option>
                            @foreach ($this->projectImportHeaders as $header)
                                <option value="{{ $header }}">{{ $header }}</option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 flex flex-wrap gap-3">
                <x-filament::button wire:click="confirmProjectImportMapping" icon="heroicon-o-check">
                    Continue to Preview
                </x-filament::button>
                <x-filament::button wire:click="resetProjectImport" color="gray" icon="heroicon-o-arrow-path">
                    Reset
                </x-filament::button>
            </div>
        </div>
    @endif

    @if (count($this->projectImportValidationErrors) > 0)
        <div class="rounded-lg border border-danger-300 bg-danger-50 p-4 dark:border-danger-600 dark:bg-danger-950/20">
            <h3 class="text-sm font-medium text-danger-800 dark:text-danger-200">
                Validation Errors ({{ count($this->projectImportValidationErrors) }})
            </h3>
            <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-danger-700 dark:text-danger-300">
                @foreach ($this->projectImportValidationErrors as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (count($this->projectImportValidationWarnings) > 0)
        <div class="rounded-lg border border-warning-300 bg-warning-50 p-4 dark:border-warning-600 dark:bg-warning-950/20">
            <h3 class="text-sm font-medium text-warning-800 dark:text-warning-200">
                Validation Warnings ({{ count($this->projectImportValidationWarnings) }})
            </h3>
            <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-warning-700 dark:text-warning-300">
                @foreach ($this->projectImportValidationWarnings as $warning)
                    <li>{{ $warning }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (count($this->projectImportPreviewData) > 0 && ! $this->projectImportCompleted)
        <div>
            <h3 class="mb-3 text-base font-medium text-gray-900 dark:text-white">
                Preview ({{ count($this->projectImportPreviewData) }} {{ \Illuminate\Support\Str::plural('project', count($this->projectImportPreviewData)) }})
            </h3>

            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="w-full divide-y divide-gray-200 text-left dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3 text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Row</th>
                            @foreach ($this->projectImportPreviewColumns as $key => $label)
                                <th class="px-4 py-3 text-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ $label }}</th>
                            @endforeach
                            <th class="px-4 py-3 text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                        @foreach ($this->projectImportPreviewData as $row)
                            <tr @class(['bg-danger-50 dark:bg-danger-950/20' => ($row['status'] ?? null) === 'error'])>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $row['row'] ?? '' }}</td>
                                @foreach ($this->projectImportPreviewColumns as $key => $label)
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900 dark:text-white">
                                        {{ data_get($row, $key, '') }}
                                    </td>
                                @endforeach
                                <td class="whitespace-nowrap px-4 py-3 text-sm">
                                    @if (($row['status'] ?? null) === 'valid')
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

            <div class="mt-4 flex flex-wrap gap-3">
                <x-filament::button
                    wire:click="importProjectImport"
                    :color="$this->projectImportHasWarnings ? 'warning' : 'success'"
                    :icon="$this->projectImportHasWarnings ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle'"
                    :disabled="$this->projectImportHasErrors"
                >
                    @if ($this->projectImportHasWarnings)
                        Proceed & Overwrite Assignments
                    @else
                        Import Projects
                    @endif
                </x-filament::button>
                <x-filament::button wire:click="resetProjectImport" color="gray" icon="heroicon-o-arrow-path">
                    Reset
                </x-filament::button>
            </div>
        </div>
    @endif

    @if ($this->projectImportCompleted)
        <div class="rounded-lg border border-success-300 bg-success-50 p-4 dark:border-success-600 dark:bg-success-950/20">
            <h3 class="text-sm font-medium text-success-800 dark:text-success-200">Import Complete</h3>
            <p class="mt-1 text-sm text-success-700 dark:text-success-300">
                {{ $this->projectImportImportedCount }} {{ \Illuminate\Support\Str::plural('project', $this->projectImportImportedCount) }} imported into the selected semester.
            </p>
        </div>
    @endif
</div>
