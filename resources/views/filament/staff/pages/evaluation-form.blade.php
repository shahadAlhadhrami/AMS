<x-filament-panels::page>
    {{-- Status banner --}}
    @if ($this->evaluation->status === 'submitted')
        <div class="rounded-lg border border-green-300 bg-green-50 p-4 dark:border-green-600 dark:bg-green-950/20">
            <p class="text-sm font-medium text-green-800 dark:text-green-200">
                This assessment has been submitted and is read-only.
                @if ($this->evaluation->unlocked_by)
                    (Previously unlocked by admin)
                @endif
            </p>
        </div>
    @endif

    {{-- Evaluation header info --}}
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Project</span>
                <p class="font-medium text-gray-900 dark:text-white">{{ $this->evaluation->project->title }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Rubric</span>
                <p class="font-medium text-gray-900 dark:text-white">{{ $this->evaluation->rubricTemplate->name }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Role</span>
                <p class="font-medium text-gray-900 dark:text-white">{{ $this->evaluation->evaluator_role }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Status</span>
                <p>
                    <x-filament::badge :color="match($this->evaluation->status) {
                        'pending' => 'gray',
                        'draft' => 'warning',
                        'submitted' => 'success',
                        default => 'gray',
                    }">
                        {{ ucfirst($this->evaluation->status) }}
                    </x-filament::badge>
                </p>
            </div>
        </div>
    </div>

    {{-- Dynamic form --}}
    {{ $this->form }}

    {{-- Action buttons --}}
    @if ($this->evaluation->status !== 'submitted')
        <div class="mt-6 flex justify-between">
            <x-filament::button
                wire:click="saveDraft"
                color="gray"
                icon="heroicon-o-bookmark"
            >
                Save Draft
            </x-filament::button>

            <x-filament::button
                wire:click="submitEvaluation"
                color="success"
                icon="heroicon-o-check-circle"
                wire:confirm="Once submitted, this assessment will be locked and you will not be able to make changes. Are you sure you want to submit?"
            >
                Submit Assessment
            </x-filament::button>
        </div>
    @endif
</x-filament-panels::page>
