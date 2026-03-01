<x-filament-panels::page>
    {{-- Project Info --}}
    <x-filament::section heading="Project Information">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Title</span>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $this->project->title }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Course</span>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">
                    {{ $this->project->course?->code }} - {{ $this->project->course?->title }}
                </p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Semester</span>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $this->project->semester?->name }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Phase Template</span>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $this->project->phaseTemplate?->name }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Specialization</span>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $this->project->specialization?->name }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Supervisor</span>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $this->project->supervisor?->name }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</span>
                <p class="mt-1">
                    <x-filament::badge :color="match($this->project->status) {
                        'setup' => 'gray',
                        'evaluating' => 'warning',
                        'completed' => 'success',
                        default => 'gray',
                    }">
                        {{ ucfirst($this->project->status) }}
                    </x-filament::badge>
                </p>
            </div>
        </div>
    </x-filament::section>

    {{-- Team Members --}}
    <x-filament::section heading="Team Members">
        @if($this->project->students->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs uppercase text-gray-500 dark:text-gray-400 border-b dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">University ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($this->project->students as $student)
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $student->name }}</td>
                                <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $student->university_id }}</td>
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
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs uppercase text-gray-500 dark:text-gray-400 border-b dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-3">Rubric</th>
                            <th class="px-4 py-3">Evaluator</th>
                            <th class="px-4 py-3">Role</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($this->project->evaluations as $evaluation)
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-3 text-gray-900 dark:text-white">
                                    {{ $evaluation->rubricTemplate?->name }}
                                </td>
                                <td class="px-4 py-3 text-gray-900 dark:text-white">
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
