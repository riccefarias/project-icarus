<x-filament-panels::page>
    <x-filament::section>
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-xl font-bold tracking-tight">{{ $this->integrationTitle }}</h2>
                <p class="mt-2 text-gray-500 dark:text-gray-400">
                    @if($integration === 'traccar-api')
                        Configure os parâmetros de conexão com a API REST do Traccar.
                    @elseif($integration === 'traccar-database')
                        Configure os parâmetros de conexão direta com o banco de dados do Traccar.
                    @endif
                </p>
            </div>
            
            <div>
                <x-filament::button tag="a" href="{{ route('filament.admin.pages.integrations-manager') }}" color="gray">
                    Voltar para o Gerenciador
                </x-filament::button>
            </div>
        </div>
        
        <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800">
            @if($integration === 'traccar-api')
                <form wire:submit.prevent="saveTraccarApiSettings">
                    <div class="space-y-6">
                        <div>
                            <x-filament::input.wrapper id="traccarApiConfig.enabled">
                                <div class="flex items-center space-x-2">
                                    <x-filament::input.checkbox id="traccarApiConfig.enabled" wire:model="traccarApiConfig.enabled" />
                                    <label for="traccarApiConfig.enabled" class="text-sm font-medium text-gray-700 dark:text-gray-300">Ativado</label>
                                </div>
                            </x-filament::input.wrapper>
                        </div>
                        
                        <div>
                            <x-filament::input.wrapper id="traccarApiConfig.url" label="URL da API">
                                <x-filament::input id="traccarApiConfig.url" type="text" wire:model="traccarApiConfig.url" placeholder="http://traccar.example.com:8082/api" />
                            </x-filament::input.wrapper>
                            @error('traccarApiConfig.url') <p class="mt-1 text-sm text-danger-500">{{ $message }}</p> @enderror
                        </div>
                        
                        <div>
                            <x-filament::input.wrapper id="traccarApiConfig.username" label="Usuário">
                                <x-filament::input id="traccarApiConfig.username" type="text" wire:model="traccarApiConfig.username" />
                            </x-filament::input.wrapper>
                            @error('traccarApiConfig.username') <p class="mt-1 text-sm text-danger-500">{{ $message }}</p> @enderror
                        </div>
                        
                        <div>
                            <x-filament::input.wrapper id="traccarApiConfig.password" label="Senha">
                                <x-filament::input id="traccarApiConfig.password" type="password" wire:model="traccarApiConfig.password" />
                            </x-filament::input.wrapper>
                            @error('traccarApiConfig.password') <p class="mt-1 text-sm text-danger-500">{{ $message }}</p> @enderror
                        </div>
                        
                        <div class="flex gap-3">
                            <x-filament::button type="submit">
                                Salvar Configurações
                            </x-filament::button>
                            
                            <x-filament::button type="button" wire:click="testTraccarApiConnection" color="secondary">
                                Testar Conexão
                            </x-filament::button>
                        </div>
                    </div>
                </form>
            @elseif($integration === 'traccar-database')
                <form wire:submit.prevent="saveTraccarDatabaseSettings">
                    <div class="space-y-6">
                        <div>
                            <x-filament::input.wrapper id="traccarDatabaseConfig.enabled">
                                <div class="flex items-center space-x-2">
                                    <x-filament::input.checkbox id="traccarDatabaseConfig.enabled" wire:model="traccarDatabaseConfig.enabled" />
                                    <label for="traccarDatabaseConfig.enabled" class="text-sm font-medium text-gray-700 dark:text-gray-300">Ativado</label>
                                </div>
                            </x-filament::input.wrapper>
                        </div>
                        
                        <div>
                            <x-filament::input.wrapper id="traccarDatabaseConfig.host" label="Host do Banco de Dados">
                                <x-filament::input id="traccarDatabaseConfig.host" type="text" wire:model="traccarDatabaseConfig.host" placeholder="localhost" />
                            </x-filament::input.wrapper>
                            @error('traccarDatabaseConfig.host') <p class="mt-1 text-sm text-danger-500">{{ $message }}</p> @enderror
                        </div>
                        
                        <div>
                            <x-filament::input.wrapper id="traccarDatabaseConfig.port" label="Porta">
                                <x-filament::input id="traccarDatabaseConfig.port" type="text" wire:model="traccarDatabaseConfig.port" placeholder="3306" />
                            </x-filament::input.wrapper>
                            @error('traccarDatabaseConfig.port') <p class="mt-1 text-sm text-danger-500">{{ $message }}</p> @enderror
                        </div>
                        
                        <div>
                            <x-filament::input.wrapper id="traccarDatabaseConfig.database" label="Nome do Banco de Dados">
                                <x-filament::input id="traccarDatabaseConfig.database" type="text" wire:model="traccarDatabaseConfig.database" placeholder="traccar" />
                            </x-filament::input.wrapper>
                            @error('traccarDatabaseConfig.database') <p class="mt-1 text-sm text-danger-500">{{ $message }}</p> @enderror
                        </div>
                        
                        <div>
                            <x-filament::input.wrapper id="traccarDatabaseConfig.username" label="Usuário">
                                <x-filament::input id="traccarDatabaseConfig.username" type="text" wire:model="traccarDatabaseConfig.username" />
                            </x-filament::input.wrapper>
                            @error('traccarDatabaseConfig.username') <p class="mt-1 text-sm text-danger-500">{{ $message }}</p> @enderror
                        </div>
                        
                        <div>
                            <x-filament::input.wrapper id="traccarDatabaseConfig.password" label="Senha">
                                <x-filament::input id="traccarDatabaseConfig.password" type="password" wire:model="traccarDatabaseConfig.password" />
                            </x-filament::input.wrapper>
                            @error('traccarDatabaseConfig.password') <p class="mt-1 text-sm text-danger-500">{{ $message }}</p> @enderror
                        </div>
                        
                        <div class="flex gap-3">
                            <x-filament::button type="submit">
                                Salvar Configurações
                            </x-filament::button>
                            
                            <x-filament::button type="button" wire:click="testTraccarDatabaseConnection" color="secondary">
                                Testar Conexão
                            </x-filament::button>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </x-filament::section>
</x-filament-panels::page>