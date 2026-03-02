<div class="space-y-4">
    {{-- Action Buttons --}}
    <div class="flex gap-3">
        <x-filament::button
            wire:click="previewCsv"
            icon="heroicon-o-eye"
            color="gray"
        >
            Preview & Validate
        </x-filament::button>

        <x-filament::button
            wire:click="downloadProjectCsvTemplate"
            icon="heroicon-o-arrow-down-tray"
            color="gray"
        >
            Download CSV Template
        </x-filament::button>
    </div>

    {{-- Validation Errors --}}
    @if (count($this->csvValidationErrors) > 0)
        <div class="rounded-lg border border-danger-300 bg-danger-50 p-4 dark:border-danger-600 dark:bg-danger-950/20">
            <h3 class="text-sm font-medium text-danger-800 dark:text-danger-200">
                Validation Errors ({{ count($this->csvValidationErrors) }})
            </h3>
            <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-danger-700 dark:text-danger-300">
                @foreach ($this->csvValidationErrors as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Preview Table --}}
    @if (count($this->csvPreviewData) > 0)
        <div class="mt-2">
            <h3 class="mb-3 text-lg font-medium text-gray-900 dark:text-white">
                Preview ({{ count($this->csvPreviewData) }} rows)
            </h3>

            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Row</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Title</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Course</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Phase Template</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Supervisor</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Students</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Reviewers</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                        @foreach ($this->csvPreviewData as $row)
                            <tr class="{{ $row['status'] === 'error' ? 'bg-danger-50 dark:bg-danger-950/20' : '' }}">
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $row['row'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $row['title'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $row['course_code'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $row['phase_template_name'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $row['supervisor'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $row['student_count'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $row['reviewer_count'] }}</td>
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
        </div>
    @elseif ($this->csvValidated)
        <div class="rounded-lg border border-warning-300 bg-warning-50 p-4 dark:border-warning-600 dark:bg-warning-950/20">
            <p class="text-sm text-warning-700 dark:text-warning-300">CSV file contains no valid data rows.</p>
        </div>
    @endif
</div>
