<x-filament-panels::page>
    {{-- Infolist --}}
    {{ $this->infolist }}

    {{-- Evaluation Scores Table --}}
    <x-filament::section heading="Evaluation Scores">
        @php
            $scores = $this->getScores();
        @endphp

        @if($scores->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs uppercase text-gray-500 dark:text-gray-400 border-b dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-3">Criterion</th>
                            <th class="px-4 py-3">Student</th>
                            <th class="px-4 py-3">Score Level</th>
                            <th class="px-4 py-3">Score</th>
                            <th class="px-4 py-3">Feedback</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($scores as $score)
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-3 text-gray-900 dark:text-white">
                                    {{ $score->criterion?->title }}
                                </td>
                                <td class="px-4 py-3 text-gray-900 dark:text-white">
                                    {{ $score->student?->name ?? 'Group' }}
                                </td>
                                <td class="px-4 py-3 text-gray-900 dark:text-white">
                                    {{ $score->scoreLevel?->label ?? '--' }}
                                </td>
                                <td class="px-4 py-3 text-gray-900 dark:text-white">
                                    {{ $score->score_awarded }} / {{ $score->criterion?->max_score }}
                                </td>
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                    {{ $score->feedback ?? '--' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400">No scores recorded yet.</p>
        @endif
    </x-filament::section>
</x-filament-panels::page>
