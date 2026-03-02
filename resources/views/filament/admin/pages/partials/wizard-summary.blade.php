<div class="space-y-6">
    {{-- Semester Summary --}}
    @php $semester = $this->getSemesterSummary(); @endphp
    @if ($semester)
        <x-filament::section>
            <x-slot name="heading">Semester Created</x-slot>
            <div class="grid grid-cols-2 gap-4 text-sm md:grid-cols-4">
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Name</span>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $semester['name'] }}</p>
                </div>
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Academic Year</span>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $semester['academic_year'] }}</p>
                </div>
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Start Date</span>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $semester['start_date'] ?? 'Not set' }}</p>
                </div>
                <div>
                    <span class="text-gray-500 dark:text-gray-400">End Date</span>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $semester['end_date'] ?? 'Not set' }}</p>
                </div>
            </div>
        </x-filament::section>
    @endif

    {{-- Phase Templates --}}
    @php $phaseTemplates = $this->getSelectedPhaseTemplateNames(); @endphp
    @if (count($phaseTemplates) > 0)
        <x-filament::section>
            <x-slot name="heading">Phase Templates Selected ({{ count($phaseTemplates) }})</x-slot>
            <ul class="list-inside list-disc space-y-1 text-sm text-gray-700 dark:text-gray-300">
                @foreach ($phaseTemplates as $name)
                    <li>{{ $name }}</li>
                @endforeach
            </ul>
        </x-filament::section>
    @endif

    {{-- Projects Created --}}
    @php $projects = $this->getProjectsSummary(); @endphp
    <x-filament::section>
        <x-slot name="heading">Projects Created ({{ count($projects) }})</x-slot>
        @if (count($projects) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-xs uppercase dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3 text-gray-500 dark:text-gray-400">Title</th>
                            <th class="px-4 py-3 text-gray-500 dark:text-gray-400">Course</th>
                            <th class="px-4 py-3 text-gray-500 dark:text-gray-400">Phase Template</th>
                            <th class="px-4 py-3 text-gray-500 dark:text-gray-400">Supervisor</th>
                            <th class="px-4 py-3 text-gray-500 dark:text-gray-400">Students</th>
                            <th class="px-4 py-3 text-gray-500 dark:text-gray-400">Reviewers</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($projects as $project)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $project['title'] }}</td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $project['course'] }}</td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $project['phase_template'] }}</td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $project['supervisor'] }}</td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $project['students'] ?: '--' }}</td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $project['reviewers'] ?: '--' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400">No projects were created. You can add projects later from the Projects page.</p>
        @endif
    </x-filament::section>
</div>
