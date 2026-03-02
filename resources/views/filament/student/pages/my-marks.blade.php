<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Semester Selector --}}
        @if ($this->getSemesters()->count() > 1)
            <x-filament::section>
                <div>
                    <label for="semester" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Semester</label>
                    <select
                        id="semester"
                        wire:model.live="selectedSemester"
                        class="w-full max-w-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm"
                    >
                        @foreach ($this->getSemesters() as $semester)
                            <option value="{{ $semester->id }}">{{ $semester->name }} ({{ $semester->academic_year }})</option>
                        @endforeach
                    </select>
                </div>
            </x-filament::section>
        @endif

        @php $data = $this->getProjectData(); @endphp

        @if ($data)
            @php
                $project = $data['project'];
                $internalMarks = $data['internalMarks'];
                $consolidatedMark = $data['consolidatedMark'];
            @endphp

            {{-- Project Info --}}
            <x-filament::section>
                <x-slot name="heading">Project Information</x-slot>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="font-medium text-gray-500 dark:text-gray-400">Project:</span>
                        <span class="ml-2 text-gray-900 dark:text-white">{{ $project->title }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-500 dark:text-gray-400">Course:</span>
                        <span class="ml-2 text-gray-900 dark:text-white">{{ $project->course->code }} - {{ $project->course->title }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-500 dark:text-gray-400">Supervisor:</span>
                        <span class="ml-2 text-gray-900 dark:text-white">{{ $project->supervisor?->name ?? 'Not assigned' }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-500 dark:text-gray-400">Semester:</span>
                        <span class="ml-2 text-gray-900 dark:text-white">{{ $project->semester->name }}</span>
                    </div>
                </div>
            </x-filament::section>

            {{-- Internal Marks (Supervisor Only) --}}
            @if ($internalMarks->isNotEmpty())
                <x-filament::section>
                    <x-slot name="heading">Internal Marks (Supervisor)</x-slot>

                    @foreach ($internalMarks as $evaluation)
                        <div class="mb-6 last:mb-0">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                                <x-heroicon-o-clipboard-document-check class="w-4 h-4" />
                                {{ $evaluation->rubricTemplate->name }}
                                <span class="text-xs font-normal text-gray-400">
                                    ({{ $evaluation->rubricTemplate->total_marks }} marks)
                                </span>
                            </h4>

                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                        <tr>
                                            <th class="px-4 py-2 text-left">Criterion</th>
                                            <th class="px-4 py-2 text-right">Score</th>
                                            <th class="px-4 py-2 text-left">Feedback</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                        @php $subtotal = 0; @endphp
                                        @foreach ($evaluation->evaluationScores as $score)
                                            @php $subtotal += (float) $score->score_awarded; @endphp
                                            <tr>
                                                <td class="px-4 py-2">{{ $score->criterion->title }}</td>
                                                <td class="px-4 py-2 text-right font-mono">
                                                    {{ number_format((float) $score->score_awarded, 2) }} / {{ number_format((float) $score->criterion->max_score, 2) }}
                                                </td>
                                                <td class="px-4 py-2 text-gray-500 dark:text-gray-400 text-xs">
                                                    {{ $score->feedback ?? '--' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-gray-50 dark:bg-gray-700 font-semibold">
                                            <td class="px-4 py-2">Subtotal</td>
                                            <td class="px-4 py-2 text-right font-mono">
                                                {{ number_format($subtotal, 2) }} / {{ $evaluation->rubricTemplate->total_marks }}
                                            </td>
                                            <td class="px-4 py-2"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            @if ($evaluation->general_feedback)
                                <div class="mt-2 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-sm text-blue-800 dark:text-blue-200">
                                    <span class="font-medium">General Feedback:</span> {{ $evaluation->general_feedback }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </x-filament::section>
            @endif

            {{-- Consolidated Marks --}}
            @if ($consolidatedMark)
                <x-filament::section>
                    <x-slot name="heading">Consolidated Marks</x-slot>

                    <div class="overflow-x-auto mb-4">
                        <table class="w-full text-sm">
                            <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-2 text-left">Source</th>
                                    <th class="px-4 py-2 text-right">Score</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                @foreach ($consolidatedMark['mark']->components as $component)
                                    <tr>
                                        <td class="px-4 py-2">{{ $component->source_label }}</td>
                                        <td class="px-4 py-2 text-right font-mono">{{ number_format((float) $component->score, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="text-center">
                            <div class="text-xs uppercase text-gray-500 dark:text-gray-400 mb-1">Calculated Total</div>
                            <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $consolidatedMark['mark']->total_calculated_score }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-xs uppercase text-gray-500 dark:text-gray-400 mb-1">Final Score</div>
                            <div class="text-lg font-bold text-primary-600 dark:text-primary-400">{{ $consolidatedMark['finalScore'] }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-xs uppercase text-gray-500 dark:text-gray-400 mb-1">Letter Grade</div>
                            <div class="text-lg font-bold text-primary-600 dark:text-primary-400">{{ $consolidatedMark['letterGrade'] }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-xs uppercase text-gray-500 dark:text-gray-400 mb-1">GPA</div>
                            <div class="text-lg font-bold text-primary-600 dark:text-primary-400">
                                {{ $consolidatedMark['gpa'] !== null ? number_format($consolidatedMark['gpa'], 2) : '--' }}
                            </div>
                        </div>
                    </div>
                </x-filament::section>
            @else
                <x-filament::section>
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        <x-heroicon-o-clock class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" />
                        <p class="text-lg font-medium">Consolidated marks are not yet available.</p>
                        <p class="text-sm mt-1">Marks will appear here once all evaluations are submitted and grades are finalized.</p>
                    </div>
                </x-filament::section>
            @endif

            <div class="text-xs text-gray-400 dark:text-gray-500 text-center">
                All data is read-only.
            </div>
        @else
            <x-filament::section>
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    No project found for the selected semester.
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
