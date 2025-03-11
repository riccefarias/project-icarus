<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\TraccarService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Integrations\IntegrationsManager::class, function ($app) {
            return new \App\Integrations\IntegrationsManager();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Forçar HTTPS em produção
        if (config('app.env') !== 'local') {
            \URL::forceScheme('https');
        }
        
        Model::unguard();
        
        // Define Super Admin (usuário com role 'admin')
        Gate::before(function (User $user, $ability) {
            return $user->hasRole('admin') ? true : null;
        });
        
        // Sync Customer with Traccar when created or updated
        Customer::created(function (Customer $customer) {
            $integrationsManager = app(\App\Integrations\IntegrationsManager::class);
            $integrationsManager->loadIntegrations();
            
            $activeIntegration = $integrationsManager->getActiveIntegration();
            if ($activeIntegration && $activeIntegration->isEnabled()) {
                $activeIntegration->syncCustomer($customer);
            }
        });
        
        Customer::updated(function (Customer $customer) {
            $integrationsManager = app(\App\Integrations\IntegrationsManager::class);
            $integrationsManager->loadIntegrations();
            
            $activeIntegration = $integrationsManager->getActiveIntegration();
            if ($activeIntegration && $activeIntegration->isEnabled()) {
                $activeIntegration->syncCustomer($customer);
            }
        });
        
        // Sync Vehicle with Traccar when created or updated
        Vehicle::created(function (Vehicle $vehicle) {
            $integrationsManager = app(\App\Integrations\IntegrationsManager::class);
            $integrationsManager->loadIntegrations();
            
            $activeIntegration = $integrationsManager->getActiveIntegration();
            if ($activeIntegration && $activeIntegration->isEnabled()) {
                $activeIntegration->syncVehicle($vehicle);
            }
        });
        
        Vehicle::updated(function (Vehicle $vehicle) {
            $integrationsManager = app(\App\Integrations\IntegrationsManager::class);
            $integrationsManager->loadIntegrations();
            
            $activeIntegration = $integrationsManager->getActiveIntegration();
            if ($activeIntegration && $activeIntegration->isEnabled()) {
                $activeIntegration->syncVehicle($vehicle);
            }
        });
    }
}
