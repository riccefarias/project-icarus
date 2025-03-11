<div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800">
    <h3 class="text-lg font-semibold">Configuração do Traccar API</h3>
    
    <p class="mt-2 text-gray-500 dark:text-gray-400">
        Configure os parâmetros de conexão para a API REST do Traccar.
    </p>
    
    <div class="mt-4 space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">URL da API</label>
                <input type="text" id="url" name="url" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" placeholder="http://traccar.exemplo.com:8082/api" value="{{ config('services.traccar.url') }}">
            </div>
            
            <div>
                <label for="enabled" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                <div class="mt-2">
                    <label class="inline-flex items-center">
                        <input type="checkbox" id="enabled" name="enabled" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:border-gray-700 dark:bg-gray-900 dark:focus:ring-primary-800" {{ config('services.traccar.enabled') ? 'checked' : '' }}>
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Ativado</span>
                    </label>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Usuário</label>
                <input type="text" id="username" name="username" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" value="{{ config('services.traccar.username') }}">
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Senha</label>
                <input type="password" id="password" name="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" value="{{ config('services.traccar.password') }}">
            </div>
        </div>
    </div>
    
    <div class="mt-6 flex justify-end space-x-3">
        <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
            Testar Conexão
        </button>
        
        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
            Salvar Configurações
        </button>
    </div>
</div>