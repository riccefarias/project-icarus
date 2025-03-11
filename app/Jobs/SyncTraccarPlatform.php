<?php

namespace App\Jobs;

use App\Integrations\IntegrationsManager;
use App\Models\Customer;
use App\Models\Equipment;
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
                Log::info('Syncing with platform: '.$activeIntegration->getName());

                // First sync from tracking platform
                $activeIntegration->syncAllFromTracking();

                // Then sync customer-device pivots
                //$this->syncCustomerDevicePivots($activeIntegration);

                Log::info('Platform sync completed successfully');
            } else {
                Log::warning('No active tracking integration available for sync');
            }
        } catch (\Exception $e) {
            Log::error('Error syncing with tracking platform: '.$e->getMessage());
        }
    }

    /**
     * Sync customer-device pivot relationships with Traccar
     * This ensures that customer users have access to their equipment devices in Traccar
     */
    protected function syncCustomerDevicePivots($integration): void
    {
        try {
            Log::info('Starting direct customer-equipment pivoting sync');
            $syncedPivots = 0;

            // Get all customer-equipment pivot relationships
            $customers = Customer::with(['equipment', 'vehicles.equipment'])->get();
            foreach ($customers as $customer) {
                if (! $customer->traccar_id) {
                    continue; // Skip customers not synced with Traccar
                }

                // Process direct equipment relations
                foreach ($customer->equipment as $equipment) {
                    if (! $equipment->traccar_id) {
                        continue; // Skip equipment not synced with Traccar
                    }

                    // Link the equipment device to the customer
                    if (method_exists($integration, 'linkDeviceToUser')) {
                        $result = $integration->linkDeviceToUser($equipment->traccar_id, $customer->traccar_id);
                        if ($result) {
                            $syncedPivots++;
                        }
                    }
                }

                // Also process equipment from customer's vehicles
                foreach ($customer->vehicles as $vehicle) {
                    if ($vehicle->equipment_id && $vehicle->equipment && $vehicle->equipment->traccar_id) {
                        // Link the vehicle's equipment device to the customer
                        if (method_exists($integration, 'linkDeviceToUser')) {
                            $result = $integration->linkDeviceToUser($vehicle->equipment->traccar_id, $customer->traccar_id);
                            if ($result) {
                                $syncedPivots++;
                            }
                        }
                    }
                }
            }

            Log::info("Synced {$syncedPivots} customer-equipment direct and vehicle pivot relationships");
        } catch (\Exception $e) {
            Log::error('Error syncing customer-device pivots: '.$e->getMessage());
        }
    }
}
