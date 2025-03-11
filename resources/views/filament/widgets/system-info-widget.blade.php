<x-filament::section>
    <div class="space-y-4">
        <h2 class="text-xl font-bold tracking-tight">Informações do Sistema</h2>
        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">PHP</h3>
                <p class="mt-1">{{ $phpVersion }}</p>
            </div>
            
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Laravel</h3>
                <p class="mt-1">{{ $laravelVersion }}</p>
            </div>
            
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Banco de Dados</h3>
                <p class="mt-1">{{ ucfirst($databaseType) }}</p>
            </div>
            
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Versão Atual</h3>
                <p class="mt-1">{{ $gitCurrentVersion }}</p>
            </div>
            
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Versão Remota</h3>
                <p class="mt-1">{{ $gitLatestVersion }}</p>
            </div>
        </div>
        
        @if ($hasUpdates)
            <div x-data="{ updateLoading: false }">
                <div class="mt-4 flex items-center space-x-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-800">
                        <x-filament::icon
                            alias="heroicon-o-arrow-up-circle"
                            icon="heroicon-o-arrow-up-circle"
                            class="h-5 w-5 text-amber-600 dark:text-amber-400"
                        />
                    </div>
                    <span class="text-sm text-amber-600 dark:text-amber-400">
                        Atualização disponível!
                    </span>
                </div>
                
                <form method="POST" action="{{ url('/update-system') }}" x-on:submit="updateLoading = true">
                    @csrf
                    <x-filament::button 
                        type="submit"
                        color="warning"
                        class="mt-3"
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
        @endif
    </div>
</x-filament::section>