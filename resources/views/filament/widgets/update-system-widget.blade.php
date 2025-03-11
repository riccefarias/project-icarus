<x-filament::section>
    <div x-data="{ updateLoading: false }">
        <div class="flex items-center space-x-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-800">
                <x-filament::icon
                    alias="heroicon-o-arrow-up-circle"
                    icon="heroicon-o-arrow-up-circle"
                    class="h-6 w-6 text-amber-600 dark:text-amber-400"
                />
            </div>
            <div class="space-y-1">
                <h3 class="text-lg font-medium text-amber-600 dark:text-amber-400">
                    Nova versão disponível!
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Atualização da versão <span class="font-semibold">{{ $currentVersion }}</span> para <span class="font-semibold">{{ $remoteVersion }}</span> disponível.
                </p>
            </div>
        </div>
        
        <form method="POST" action="{{ url('/update-system') }}" x-on:submit="updateLoading = true" class="mt-4">
            @csrf
            <x-filament::button 
                type="submit"
                color="warning"
                :disabled="false"
                x-bind:disabled="updateLoading"
            >
                <span x-show="!updateLoading">Atualizar Sistema</span>
                <span x-show="updateLoading" class="flex items-center gap-1">
                    <x-filament::loading-indicator class="h-4 w-4" />
                    Atualizando...
                </span>
            </x-filament::button>
        </form>
    </div>
</x-filament::section>