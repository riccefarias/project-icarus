<x-filament::page>
    <div class="grid grid-cols-1 gap-6">
        @if (method_exists($this, 'getHeaderWidgets'))
            <div>
                @foreach ($this->getHeaderWidgets() as $widget)
                    @livewire($widget)
                @endforeach
            </div>
        @endif
        
        <div>
            <x-filament::section>
                <h2 class="text-xl font-bold tracking-tight">Bem-vindo ao Icarus!</h2>
                <p class="mt-2 text-gray-500 dark:text-gray-400">
                    Sistema de gestão de veículos, equipamentos e monitoramento para empresas.
                </p>
                
                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-filament::card>
                        <h3 class="text-lg font-medium">Gerenciamento de Clientes</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Cadastre e gerencie todos os seus clientes, associando veículos e equipamentos.
                        </p>
                        <x-filament::button
                            tag="a"
                            href="{{ url('/resources/customers') }}"
                            icon="heroicon-m-arrow-right"
                            icon-position="after"
                            class="mt-4"
                        >
                            Acessar Clientes
                        </x-filament::button>
                    </x-filament::card>
                    
                    <x-filament::card>
                        <h3 class="text-lg font-medium">Rastreamento de Veículos</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Monitore todos os seus veículos com integrações com plataformas de rastreamento.
                        </p>
                        <x-filament::button
                            tag="a"
                            href="{{ url('/resources/vehicles') }}"
                            icon="heroicon-m-arrow-right"
                            icon-position="after"
                            class="mt-4"
                        >
                            Acessar Veículos
                        </x-filament::button>
                    </x-filament::card>
                </div>
            </x-filament::section>
        </div>
        
        @if (method_exists($this, 'getFooterWidgets'))
            <div>
                @foreach ($this->getFooterWidgets() as $widget)
                    @livewire($widget)
                @endforeach
            </div>
        @endif
    </div>
</x-filament::page>