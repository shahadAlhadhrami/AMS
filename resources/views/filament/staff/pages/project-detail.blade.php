<x-filament-panels::page>
    {{-- Project Information --}}
    <x-filament::section heading="Project Information">
        <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div class="flex flex-col gap-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Title</dt>
                <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $this->project->title }}</dd>
            </div>
            <div class="flex flex-col gap-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Course</dt>
                <dd class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ $this->project->course?->code }} — {{ $this->project->course?->title }}
                </dd>
            </div>
            <div class="flex flex-col gap-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Semester</dt>
                <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $this->project->semester?->name }}</dd>
            </div>
            <div class="flex flex-col gap-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Phase Template</dt>
                <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $this->project->phaseTemplate?->name }}</dd>
            </div>
            <div class="flex flex-col gap-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Specialization</dt>
                <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $this->project->specialization?->name }}</dd>
            </div>
            <div class="flex flex-col gap-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Supervisor</dt>
                <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $this->project->supervisor?->name }}</dd>
            </div>
            <div class="flex flex-col gap-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</dt>
                <dd class="mt-1">
                    <x-filament::badge :color="match($this->project->status) {
                        'setup' => 'gray',
                        'evaluating' => 'warning',
                        'completed' => 'success',
                        default => 'gray',
                    }">
                        {{ ucfirst($this->project->status) }}
                    </x-filament::badge>
                </dd>
            </div>
        </dl>
    </x-filament::section>

    {{-- Team Members --}}
    <x-filament::section heading="Team Members">
        @if($this->project->students->isNotEmpty())
            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">University ID</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($this->project->students as $student)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $student->name }}</td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $student->university_id }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400">No students assigned yet.</p>
        @endif
    </x-filament::section>

    {{-- Evaluation Status --}}
    <x-filament::section heading="Evaluation Status">
        @if($this->project->evaluations->isNotEmpty())
            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Rubric</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Evaluator</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Role</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($this->project->evaluations as $evaluation)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                    {{ $evaluation->rubricTemplate?->name }}
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                    {{ $evaluation->evaluator?->name }}
                                </td>
                                <td class="px-4 py-3">
                                    <x-filament::badge :color="match($evaluation->evaluator_role) {
                                        'Supervisor' => 'info',
                                        'Reviewer' => 'warning',
                                        default => 'gray',
                                    }">
                                        {{ $evaluation->evaluator_role }}
                                    </x-filament::badge>
                                </td>
                                <td class="px-4 py-3">
                                    <x-filament::badge :color="match($evaluation->status) {
                                        'pending' => 'gray',
                                        'draft' => 'warning',
                                        'submitted' => 'success',
                                        default => 'gray',
                                    }">
                                        {{ ucfirst($evaluation->status) }}
                                    </x-filament::badge>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400">No evaluations created yet.</p>
        @endif
    </x-filament::section>
</x-filament-panels::page>
