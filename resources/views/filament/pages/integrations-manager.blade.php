<x-filament-panels::page>
    <x-filament::section>
        <h2 class="text-xl font-bold tracking-tight mb-4">Gerenciador de Integrações</h2>
        
        <p class="text-gray-500 dark:text-gray-400 mb-6">
            Configure as integrações do sistema com serviços externos para rastreamento, pagamentos, e outras funcionalidades.
        </p>
        
            
        @foreach($categories as $categoryKey => $category)
            <div class="mb-8">
                <h3 class="text-lg font-bold tracking-tight mb-2">{{ $category['name'] }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ $category['description'] }}</p>
                
                <div class="overflow-hidden bg-white shadow dark:bg-gray-800 sm:rounded-md">
                    <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($category['integrations'] as $integrationKey => $integration)
                            <li>
                                <div class="flex items-center px-4 py-4 sm:px-6">
                                    <div class="flex min-w-0 flex-1 items-center">
                                        <div class="min-w-0 flex-1 px-4 md:grid md:grid-cols-2 md:gap-4">
                                            <div>
                                                <p class="text-sm font-medium text-primary-600 dark:text-primary-400">{{ $integration['name'] }}</p>
                                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $integration['description'] }}</p>
                                            </div>
                                            <div class="hidden md:block">
                                                <div class="flex items-center space-x-2">
                                                    @if($integration['enabled'] ?? false)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                                            Configurada
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        @if($integrationKey == 'traccar_api')
                                            <x-filament::button 
                                                type="button"
                                                color="gray"
                                                tag="a"
                                                href="{{ route('filament.admin.pages.integration-settings', ['integration' => 'traccar-api']) }}"
                                            >
                                                Configurar
                                            </x-filament::button>
                                        @elseif($integrationKey == 'traccar_database')
                                            <x-filament::button 
                                                type="button"
                                                color="gray"
                                                tag="a"
                                                href="{{ route('filament.admin.pages.integration-settings', ['integration' => 'traccar-database']) }}"
                                            >
                                                Configurar
                                            </x-filament::button>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endforeach
    </x-filament::section>
</x-filament-panels::page>