<div
    x-data="{
        openModal: false,
        syncPlatform: function() {
            this.openModal = false;
            window.location.href = '{{ route('filament.admin.pages.sync-platform') }}';
        }
    }"
>
    <button
        type="button"
        class="flex items-center justify-center w-10 h-10 rounded-full hover:bg-gray-500/5 focus:bg-primary-500/10 outline-none transition text-primary-500"
        title="Sincronizar Plataforma"
        x-on:click="openModal = true"
    >
        <x-filament::icon
            alias="heroicon-o-arrow-path"
            icon="heroicon-o-arrow-path"
            class="h-5 w-5"
        />
    </button>

    <!-- Modal de confirmação -->
    <div
        x-show="openModal"
        x-transition.opacity
        class="fixed inset-0 z-40 flex items-center justify-center"
        style="display: none;"
    >
        <div
            x-on:click="openModal = false"
            class="fixed inset-0 bg-gray-900/50 dark:bg-gray-900/75"
        ></div>

        <div
            x-on:click.outside="openModal = false"
            x-transition
            x-trap.inert.noscroll="openModal"
            class="relative max-h-[90vh] max-w-lg overflow-y-auto bg-white shadow-xl rounded-xl p-6 dark:bg-gray-800"
        >
            <div class="flex items-center gap-4">
                <div class="text-primary-500 dark:text-primary-400 flex-shrink-0">
                    <x-filament::icon
                        alias="heroicon-o-arrow-path"
                        icon="heroicon-o-arrow-path"
                        class="h-6 w-6"
                    />
                </div>

                <div class="flex-1">
                    <h3 class="text-xl font-bold tracking-tight">
                        Sincronizar dados da plataforma
                    </h3>

                    <p class="mt-2 text-gray-500 dark:text-gray-400">
                        Esta ação irá buscar todos os dados da plataforma de rastreamento ativa. Isso pode levar algum tempo.
                    </p>
                </div>
            </div>

            <div class="mt-6 flex gap-3 justify-end">
                <x-filament::button
                    color="gray"
                    x-on:click="openModal = false"
                >
                    Cancelar
                </x-filament::button>

                <x-filament::button
                    x-on:click="syncPlatform()"
                >
                    Sincronizar
                </x-filament::button>
            </div>
        </div>
    </div>
</div>