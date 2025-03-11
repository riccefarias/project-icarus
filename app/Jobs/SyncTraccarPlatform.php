<?php

namespace App\Jobs;

use App\Integrations\IntegrationsManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncTraccarPlatform implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Starting TraccarPlatform sync job');
            
            $integrationsManager = app(IntegrationsManager::class);
            $integrationsManager->loadIntegrations();
            
            $activeIntegration = $integrationsManager->getActiveIntegration(IntegrationsManager::TYPE_TRACKING);
            
            if ($activeIntegration && $activeIntegration->isEnabled()) {
                Log::info('Syncing with platform: ' . $activeIntegration->getName());
                $activeIntegration->syncAllFromTracking();
                Log::info('Platform sync completed successfully');
            } else {
                Log::warning('No active tracking integration available for sync');
            }
        } catch (\Exception $e) {
            Log::error('Error syncing with tracking platform: ' . $e->getMessage());
        }
    }
}