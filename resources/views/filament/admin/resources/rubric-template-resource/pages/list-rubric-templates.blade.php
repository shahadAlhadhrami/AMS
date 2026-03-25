<x-filament-panels::page>
    {{-- Breadcrumb navigation --}}
    <nav class="flex items-center gap-1 text-sm text-gray-600 dark:text-gray-400 mb-4 flex-wrap">
        <button
            wire:click="navigateToFolder(null)"
            class="flex items-center gap-1 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
        >
            <x-heroicon-o-folder class="w-4 h-4" />
            <span>All Templates</span>
        </button>

        @foreach ($this->getBreadcrumbs() as $crumb)
            <x-heroicon-o-chevron-right class="w-3 h-3 text-gray-400" />
            <button
                wire:click="navigateToFolder({{ $crumb['id'] }})"
                class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
            >
                {{ $crumb['name'] }}
            </button>
        @endforeach
    </nav>

    {{-- Subfolders grid --}}
    @php $subfolders = $this->getSubfolders(); @endphp

    @if ($subfolders->isNotEmpty())
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 mb-6">
            @foreach ($subfolders as $folder)
                <button
                    wire:click="navigateToFolder({{ $folder->id }})"
                    class="group flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-primary-400 dark:hover:border-primary-500 hover:shadow-md transition-all text-left"
                >
                    <x-heroicon-o-folder class="w-10 h-10 text-yellow-400 group-hover:text-yellow-500 transition-colors" />
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200 text-center leading-tight line-clamp-2">
                        {{ $folder->name }}
                    </span>
                    <span class="text-xs text-gray-400 dark:text-gray-500">
                        {{ $folder->creator->name ?? '—' }}
                    </span>
                </button>
            @endforeach
        </div>
    @endif

    {{-- Standard Filament table --}}
    {{ $this->table }}
</x-filament-panels::page>
