<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filters --}}
        <x-filament::section>
            <x-slot name="heading">Filters</x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="semester" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Semester</label>
                    <select
                        id="semester"
                        wire:model.live="selectedSemester"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm"
                    >
                        <option value="">-- Select Semester --</option>
                        @foreach ($this->getSemesters() as $semester)
                            <option value="{{ $semester->id }}">{{ $semester->name }} ({{ $semester->academic_year }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="course" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Course (Optional)</label>
                    <select
                        id="course"
                        wire:model.live="selectedCourse"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm"
                    >
                        <option value="">-- All Courses --</option>
                        @foreach ($this->getCourses() as $course)
                            <option value="{{ $course->id }}">{{ $course->code }} - {{ $course->title }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </x-filament::section>

        {{-- Preview Table --}}
        @if ($selectedSemester)
            @php $preview = $this->getPreviewData(); @endphp

            @if ($preview->isNotEmpty())
                <x-filament::section>
                    <x-slot name="heading">Preview ({{ $preview->count() }} records)</x-slot>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-3">University ID</th>
                                    <th class="px-4 py-3">Student Name</th>
                                    <th class="px-4 py-3">Project Title</th>
                                    <th class="px-4 py-3 text-right">Calculated</th>
                                    <th class="px-4 py-3 text-right">Override</th>
                                    <th class="px-4 py-3 text-right">Final Score</th>
                                    <th class="px-4 py-3 text-center">Grade</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                @foreach ($preview as $row)
                                    <tr class="bg-white dark:bg-gray-800">
                                        <td class="px-4 py-3 font-mono">{{ $row['university_id'] }}</td>
                                        <td class="px-4 py-3">{{ $row['student_name'] }}</td>
                                        <td class="px-4 py-3">{{ Str::limit($row['project_title'], 40) }}</td>
                                        <td class="px-4 py-3 text-right">{{ $row['calculated_score'] }}</td>
                                        <td class="px-4 py-3 text-right text-gray-400">{{ $row['override_score'] ?? '--' }}</td>
                                        <td class="px-4 py-3 text-right font-semibold">{{ $row['final_score'] }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-primary-100 text-primary-800 dark:bg-primary-800 dark:text-primary-200">
                                                {{ $row['letter_grade'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-filament::section>

                {{-- Export Actions --}}
                <div class="flex gap-3">
                    <x-filament::button wire:click="exportCsv" icon="heroicon-o-document-text">
                        Export CSV
                    </x-filament::button>

                    <x-filament::button wire:click="exportPdf" icon="heroicon-o-document" color="gray">
                        Export PDF
                    </x-filament::button>
                </div>
            @else
                <x-filament::section>
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        No consolidated marks found for the selected filters.
                    </div>
                </x-filament::section>
            @endif
        @else
            <x-filament::section>
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    Select a semester to preview grades.
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
