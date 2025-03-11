<?php

namespace App\Filament\Pages;

use App\Integrations\IntegrationsManager;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\Attributes\On;

class SyncPlatform extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    
    protected static ?string $navigationLabel = 'Sincronizar Plataforma';
    
    protected static ?string $title = 'Sincronização com Plataforma';
    
    protected static ?string $navigationGroup = 'Operações';
    
    protected static ?int $navigationSort = 1;
    
    // Torne a página visível no menu de navegação
    protected static bool $shouldRegisterNavigation = true;
    
    protected static string $view = 'filament.pages.sync-platform';
    
    public function mount(): void
    {
        $this->syncPlatform();
    }
    
    #[On('syncPlatform')]
    public function syncPlatform(): void
    {
        $integrationsManager = app(IntegrationsManager::class);
        $integrationsManager->loadIntegrations();
        
        $activeIntegration = $integrationsManager->getActiveIntegration();
        
        if ($activeIntegration && $activeIntegration->isEnabled()) {
            // Envia o trabalho para a fila em vez de executar sincronicamente
            \App\Jobs\SyncTraccarPlatform::dispatch();
            
            Notification::make()
                ->title('Sincronização iniciada')
                ->body('A sincronização com a plataforma foi enviada para processamento em segundo plano.')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Erro de sincronização')
                ->body('Não há integração ativa configurada. Por favor, configure uma integração primeiro.')
                ->danger()
                ->send();
        }
        
        // Redirecionar para o dashboard após o processo
        redirect()->to(route('filament.admin.pages.dashboard'));
    }
}